<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Club_member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    private function activeMemberships(Person $person)
    {
        return Club_member::query()
            ->join('clubs', 'club_members.club_id', '=', 'clubs.id')
            ->where('club_members.person_id', $person->id)
            ->where('club_members.status', 'active')
            ->orderByRaw("CASE
                WHEN club_members.role = 'president' THEN 1
                WHEN club_members.role = 'board'     THEN 2
                WHEN club_members.role = 'member'    THEN 3
                ELSE 4
            END")
            ->orderBy('clubs.name')
            ->get([
                'club_members.id as membership_id',
                'club_members.club_id',
                'club_members.role as club_role',
                'club_members.position',
                'clubs.name as club_name',
                'clubs.logo as club_logo',
                'clubs.category as club_category',
            ])
            ->map(function ($membership) {
                $membership->club_logo_url = $membership->club_logo ? url('storage/' . $membership->club_logo) : null;
                return $membership;
            });
    }

    private function selectedMembership(Request $request, Person $person)
    {
        if ($person->role !== 'user') {
            return null;
        }

        $memberships = $this->activeMemberships($person);

        if ($memberships->count() === 1) {
            $selected = $memberships->first();
            $request->session()->put('selected_club_id', $selected->club_id);
            $request->session()->put('selected_membership_id', $selected->membership_id);
            return [$selected, $memberships];
        }

        $selectedMembershipId = $request->session()->get('selected_membership_id');
        $selected = $memberships->firstWhere('membership_id', $selectedMembershipId) ?? $memberships->first();

        if ($selected) {
            $request->session()->put('selected_club_id', $selected->club_id);
            $request->session()->put('selected_membership_id', $selected->membership_id);
        }

        return [$selected, $memberships];
    }

    public function authenticatedResponse(Request $request, Person $person, string $message = 'Connexion réussie')
    {
        [$selected, $memberships] = $this->selectedMembership($request, $person) ?? [null, collect()];

        return response()->json([
            'message'   => $message,
            'requires_club_selection' => false,
            'memberships' => $memberships->values(),
            'user'      => [
                'id'                 => $person->id,
                'first_name'         => $person->first_name,
                'last_name'          => $person->last_name,
                'email'              => $person->email,
                'avatar'             => $person->avatar,
                'avatar_url'         => $person->avatar ? url('storage/' . $person->avatar) : null,
                'member_code'        => $person->member_code,
                'two_factor_enabled' => $person->two_factor_enabled,
                'club_id'            => $selected->club_id ?? null,
            ],
            'role'      => $person->role,
            'club_role' => $selected->club_role ?? null,
            'club_id'   => $selected->club_id ?? null,
            'membership_id' => $selected->membership_id ?? null,
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:persons,email',
            'password'   => 'required|string|min:6|confirmed',
            'cne'        => 'nullable|string|unique:persons,cne',
            'phone'      => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
        }

        try {
            $person = Person::create([
                'first_name'  => $request->first_name,
                'last_name'   => $request->last_name,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
                'cne'         => $request->cne,
                'phone'       => $request->phone,
                'member_code' => $this->generateMemberCode(),
                'is_active'   => true,
            ]);

            Log::info('User registered', ['person_id' => $person->id]);

            return response()->json(['message' => 'Inscription réussie', 'user' => $person], 201);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'inscription'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $person = Person::where('email', $request->email)->first();

            if (!$person || !$person->password || !Hash::check($request->password, $person->password)) {
                return response()->json(['message' => 'Email ou mot de passe incorrect'], 401);
            }

            Auth::login($person, true);

            if (!$person->is_active) {
                Auth::logout();
                return response()->json(['message' => 'Compte désactivé'], 403);
            }

            // ✅ 2FA CHECK — if enabled, pause login and wait for TOTP code
            if ($person->two_factor_enabled) {
                Auth::logout();

                // Store pending with 10-minute expiry
                $request->session()->put('2fa_pending_person_id', [
                    'id'         => $person->id,
                    'expires_at' => now()->addMinutes(10)->timestamp,
                ]);
                $request->session()->save();

                return response()->json([
                    'requires_2fa' => true,
                    'person_id'    => $person->id,
                    'message'      => '2FA requis',
                ], 200);
            }

            // No 2FA — normal login
            $request->session()->regenerate();

            return $this->authenticatedResponse($request, $person);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la connexion'], 500);
        }
    }

    public function verifySession(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }

            $person = Auth::user();

            if (!$person) {
                return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            }

            if (!$person->is_active) {
                Auth::logout();
                return response()->json(['message' => 'Compte désactivé'], 403);
            }

            return $this->authenticatedResponse($request, $person, 'Session valide');

        } catch (\Exception $e) {
            Log::error('Session verification error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la vérification de session'], 500);
        }
    }

    public function selectClub(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'membership_id' => 'required|integer|exists:club_members,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
        }

        $person = $request->user();
        if (!$person) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $membership = Club_member::where('id', $request->membership_id)
            ->where('person_id', $person->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Adhésion non trouvée pour cet utilisateur'], 404);
        }

        $request->session()->put('selected_club_id', $membership->club_id);
        $request->session()->put('selected_membership_id', $membership->id);

        return $this->authenticatedResponse($request, $person, 'Club sélectionné');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Déconnecté avec succès'], 200);
    }

    public function profile(Request $request)
    {
        try {
            $person = $request->user();
            if (!$person) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }

            $person->avatar_url = $person->avatar ? url('storage/' . $person->avatar) : null;

            return response()->json(['user' => $person], 200);
        } catch (\Exception $e) {
            Log::error('Profile error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur'], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:100',
            'last_name'  => 'sometimes|required|string|max:100',
            'phone'      => 'nullable|string|max:20',
            'avatar'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
        }

        try {
            $person = $request->user();
            if (!$person) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }

            $data = $request->only(['first_name', 'last_name', 'phone']);

            if ($request->hasFile('avatar')) {
                if ($person->avatar && \Storage::disk('public')->exists($person->avatar)) {
                    \Storage::disk('public')->delete($person->avatar);
                }
                $avatarPath  = $request->file('avatar')->store('persons/avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            $person->update($data);
            $person->refresh();

            Log::info('Profile updated', ['person_id' => $person->id]);

            return response()->json([
                'message' => 'Profil mis à jour',
                'user'    => [
                    'id'                 => $person->id,
                    'first_name'         => $person->first_name,
                    'last_name'          => $person->last_name,
                    'email'              => $person->email,
                    'phone'              => $person->phone,
                    'avatar'             => $person->avatar,
                    'avatar_url'         => $person->avatar ? url('storage/' . $person->avatar) : null,
                    'member_code'        => $person->member_code,
                    'role'               => $person->role,
                    'two_factor_enabled' => $person->two_factor_enabled,
                    'is_active'          => $person->is_active,
                    'club_id'            => $person->club_id ?? null,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    public function setupAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:200',
            'phone'   => 'required|string|max:20',
            'cne'     => 'required|string|max:50',
            'apogee'  => 'nullable|string|max:50',
            'filiere' => 'nullable|string|max:100',
            'niveau'  => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $person = $request->user();

            if (!$person) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }

            $nameParts = preg_split('/\s+/', trim($request->name), 2);

            $person->first_name = $nameParts[0] ?? $person->first_name;
            $person->last_name = $nameParts[1] ?? $person->last_name;
            $person->phone = $request->phone;
            $person->cne = $request->cne;

            foreach (['apogee', 'filiere', 'niveau'] as $field) {
                if (Schema::hasColumn('persons', $field)) {
                    $person->{$field} = $request->{$field};
                }
            }

            $person->save();
            $person->refresh();

            Log::info('Account setup updated', ['person_id' => $person->id]);

            return response()->json([
                'message' => 'Compte mis à jour avec succès',
                'user' => [
                    'id'                 => $person->id,
                    'first_name'         => $person->first_name,
                    'last_name'          => $person->last_name,
                    'name'               => trim($person->first_name . ' ' . $person->last_name),
                    'email'              => $person->email,
                    'phone'              => $person->phone,
                    'cne'                => $person->cne,
                    'apogee'             => $person->apogee ?? null,
                    'filiere'            => $person->filiere ?? null,
                    'niveau'             => $person->niveau ?? null,
                    'avatar'             => $person->avatar,
                    'avatar_url'         => $person->avatar ? url('storage/' . $person->avatar) : null,
                    'member_code'        => $person->member_code,
                    'role'               => $person->role,
                    'two_factor_enabled' => $person->two_factor_enabled,
                    'is_active'          => $person->is_active,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Account setup error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du compte'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $person      = $request->user();
        $hasPassword = !empty($person->password);

        $validator = Validator::make($request->all(), [
            'current_password'      => $hasPassword ? 'required|string' : 'nullable',
            'new_password'          => 'required|string|min:6',
            'new_password_confirmation' => 'required|string|same:new_password',
        ], [
            'new_password_confirmation.same' => 'Les mots de passe ne correspondent pas',
            'new_password.min'               => 'Le mot de passe doit contenir au moins 6 caractères',
            'current_password.required'      => 'Le mot de passe actuel est requis',
            'new_password.required'          => 'Le nouveau mot de passe est requis',
            'new_password_confirmation.required' => 'La confirmation du mot de passe est requise',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
        }

        try {
            if (!$person) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }

            if ($hasPassword && !Hash::check($request->current_password, $person->password)) {
                return response()->json(['message' => 'Le mot de passe actuel est incorrect'], 401);
            }

            $person->update(['password' => Hash::make($request->new_password)]);

            Log::info('Password changed', ['person_id' => $person->id]);

            return response()->json(['message' => 'Mot de passe changé avec succès'], 200);

        } catch (\Exception $e) {
            Log::error('Password change error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du changement de mot de passe'], 500);
        }
    }

    private function generateMemberCode()
    {
        do {
            $code = 'MBR' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Person::where('member_code', $code)->exists());
        return $code;
    }
}
