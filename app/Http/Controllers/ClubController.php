<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Club_member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ClubController extends Controller
{
    private function addImageUrls($club)
    {
        $logoPath = $club->logo ?? null;
        if ($logoPath && str_starts_with($logoPath, 'public/')) {
            $logoPath = substr($logoPath, 7);
        }
        $coverPath = $club->cover_image ?? null;
        if ($coverPath && str_starts_with($coverPath, 'public/')) {
            $coverPath = substr($coverPath, 7);
        }
        $club->logo_url = $logoPath ? url('storage/' . $logoPath) : null;
        $club->cover_image_url = $coverPath ? url('storage/' . $coverPath) : null;
        return $club;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255|unique:clubs,name',
            'code'         => 'nullable|string|max:50|unique:clubs,code',
            'description'  => 'required|string',
            'mission'      => 'nullable|string',
            'instagram_url'=> 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'contact_email'=> 'nullable|email|max:255',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'category'     => 'required|string|max:100',
            'founding_year'=> 'required|integer|min:1900|max:' . date('Y'),
            'is_public'    => 'boolean',
            'total_members'=> 'nullable|integer|min:0',
            'active_members'=>'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('clubs/logos', 'public');
            }
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('clubs/covers', 'public');
            }
            if (empty($data['code'])) {
                $data['code'] = Str::slug($data['name']);
            }
            $club = Club::create($data);
            $this->addImageUrls($club);
            return response()->json(['message' => 'Club créé avec succès', 'club' => $club], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la création du club', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'sometimes|required|string|max:255|unique:clubs,name,' . $id,
            'code'         => 'nullable|string|max:50|unique:clubs,code,' . $id,
            'description'  => 'sometimes|required|string',
            'mission'      => 'nullable|string',
            'instagram_url'=> 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'contact_email'=> 'nullable|email|max:255',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'category'     => 'sometimes|required|string|max:100',
            'founding_year'=> 'sometimes|required|integer|min:1900|max:' . date('Y'),
            'is_public'    => 'boolean',
            'total_members'=> 'nullable|integer|min:0',
            'active_members'=>'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
        }

        try {
            $person = $request->user();
            $club = Club::findOrFail($id);

            if ($person && $person->role !== 'admin') {
                $membership = Club_member::where('person_id', $person->id)
                    ->where('club_id', $club->id)
                    ->whereIn('role', ['president', 'board'])
                    ->where('status', 'active')
                    ->first();

                if (!$membership) {
                    return response()->json(['message' => 'Vous n\'avez pas accès à la gestion de ce club'], 403);
                }
            }

            $data = $request->all();

            if ($request->hasFile('logo')) {
                if ($club->logo && Storage::disk('public')->exists($club->logo)) {
                    Storage::disk('public')->delete($club->logo);
                }
                $data['logo'] = $request->file('logo')->store('clubs/logos', 'public');
            }
            if ($request->hasFile('cover_image')) {
                if ($club->cover_image && Storage::disk('public')->exists($club->cover_image)) {
                    Storage::disk('public')->delete($club->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('clubs/covers', 'public');
            }

            $club->update($data);
            $this->addImageUrls($club);
            return response()->json(['message' => 'Club mis à jour avec succès', 'club' => $club], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Club non trouvé'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour du club', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Club::query();
            if ($request->has('category')) $query->where('category', $request->category);
            if ($request->has('is_public')) $query->where('is_public', $request->boolean('is_public'));
            if ($request->has('search')) $query->where('name', 'like', '%' . $request->search . '%');
            $query->select('clubs.*')
                ->selectSub(function ($q) {
                    $q->from('club_members')
                        ->selectRaw('count(*)')
                        ->whereColumn('club_members.club_id', 'clubs.id');
                }, 'total_members_count')
                ->selectSub(function ($q) {
                    $q->from('club_members')
                        ->selectRaw('count(*)')
                        ->whereColumn('club_members.club_id', 'clubs.id')
                        ->where('club_members.status', 'active');
                }, 'active_members_count');
            $query->orderBy($request->get('order_by', 'created_at'), $request->get('order_dir', 'desc'));
            $clubs = $query->get();
            $clubs->each(fn($club) => $this->addImageUrls($club));
            return response()->json($clubs, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des clubs', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $club = Club::findOrFail($id);
            $this->addImageUrls($club);
            return response()->json($club, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Club non trouvé'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur', 'error' => $e->getMessage()], 500);
        }
    }

    public function publicDetail($id)
    {
        try {
            $club = Club::findOrFail($id);
            $this->addImageUrls($club);

            $members = DB::table('club_members')
                ->join('persons', 'club_members.person_id', '=', 'persons.id')
                ->where('club_members.club_id', $id)
                ->where('club_members.status', 'active')
                ->select(
                    'club_members.id',
                    'club_members.person_id',
                    'club_members.club_id',
                    'club_members.role',
                    'club_members.status',
                    'club_members.position',
                    'persons.first_name',
                    'persons.last_name',
                    'persons.email',
                    'persons.phone',
                    'persons.avatar'
                )
                ->get()
                ->map(function ($member) {
                    $member->avatar_url = $member->avatar ? url('storage/' . $member->avatar) : null;
                    return $member;
                });

            $events = DB::table('events')
                ->where('club_id', $id)
                ->orderBy('event_date', 'desc')
                ->select(
                    'id',
                    'club_id',
                    'title',
                    'description',
                    'category',
                    'event_date',
                    'location',
                    'status',
                    'banner_image',
                    'created_at'
                )
                ->get()
                ->map(function ($event) {
                    $event->banner_url = $event->banner_image ? url('storage/' . $event->banner_image) : null;
                    return $event;
                });

            return response()->json([
                'club' => $club,
                'members' => $members,
                'events' => $events,
                'categories' => $events->pluck('category')->filter()->unique()->values(),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Club non trouvé'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur', 'error' => $e->getMessage()], 500);
        }
    }

    public function showByCode($code)
    {
        try {
            $club = Club::where('code', $code)->firstOrFail();
            $this->addImageUrls($club);
            return response()->json($club, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Club non trouvé'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $club = Club::findOrFail($id);
            if ($club->logo && Storage::disk('public')->exists($club->logo)) Storage::disk('public')->delete($club->logo);
            if ($club->cover_image && Storage::disk('public')->exists($club->cover_image)) Storage::disk('public')->delete($club->cover_image);
            $club->delete();
            return response()->json(['message' => 'Club supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur'], 500);
        }
    }

    public function statistics($id)
    {
        try {
            $club = Club::findOrFail($id);
            return response()->json([
                'club_id'      => $club->id,
                'club_name'    => $club->name,
                'total_members'=> $club->total_members ?? 0,
                'active_members'=> $club->active_members ?? 0,
                'founding_year'=> $club->founding_year,
                'years_active' => $club->founding_year ? (date('Y') - $club->founding_year) : 0,
                'category'     => $club->category,
                'is_public'    => $club->is_public,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Club non trouvé'], 404);
        }
    }

    // Président uniquement
    public function getMyClub(Request $request)
    {
        try {
            $person = $request->user();
            if (!$person) return response()->json(['message' => 'Non authentifié'], 401);

            $result = DB::table('club_members')
                ->join('clubs', 'club_members.club_id', '=', 'clubs.id')
                ->where('club_members.person_id', $person->id)
                ->where('club_members.role', 'president')
                ->where('club_members.status', 'active')
                ->select('clubs.*', 'club_members.id as membership_id')
                ->first();

            if (!$result) {
                return response()->json(['message' => 'Vous n\'êtes président d\'aucun club'], 403);
            }

            $this->addImageUrls($result);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération du club', 'error' => $e->getMessage()], 500);
        }
    }

    // ← NOUVELLE MÉTHODE : Président ET Bureau
    public function getMyClubInfo(Request $request)
    {
        try {
            $person = $request->user();
            if (!$person) return response()->json(['message' => 'Non authentifié'], 401);

            $result = DB::table('club_members')
                ->join('clubs', 'club_members.club_id', '=', 'clubs.id')
                ->where('club_members.person_id', $person->id)
                ->whereIn('club_members.role', ['president', 'board'])
                ->where('club_members.status', 'active')
                ->select(
                    'clubs.id', 'clubs.name', 'clubs.code', 'clubs.description',
                    'clubs.mission', 'clubs.instagram_url', 'clubs.linkedin_url',
                    'clubs.facebook_url', 'clubs.contact_email',
                    'clubs.logo', 'clubs.cover_image', 'clubs.category',
                    'clubs.founding_year', 'clubs.is_public', 'clubs.total_members',
                    'clubs.active_members', 'clubs.created_at', 'clubs.updated_at',
                    'club_members.role as member_role'
                )
                ->first();

            if (!$result) {
                return response()->json(['message' => 'Aucun club trouvé pour cet utilisateur'], 403);
            }

            $this->addImageUrls($result);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération du club', 'error' => $e->getMessage()], 500);
        }
    }

    public function getDashboardSummary(Request $request)
    {
        try {
            $person = $request->user();
            if (!$person) return response()->json(['message' => 'Non authentifié'], 401);

            $membership = DB::table('club_members')
                ->join('clubs', 'club_members.club_id', '=', 'clubs.id')
                ->where('club_members.person_id', $person->id)
                ->whereIn('club_members.role', ['president', 'board'])
                ->where('club_members.status', 'active')
                ->select(
                    'club_members.role as member_role',
                    'clubs.id',
                    'clubs.name',
                    'clubs.code',
                    'clubs.description',
                    'clubs.instagram_url',
                    'clubs.linkedin_url',
                    'clubs.facebook_url',
                    'clubs.contact_email',
                    'clubs.logo',
                    'clubs.cover_image',
                    'clubs.category',
                    'clubs.founding_year',
                    'clubs.is_public',
                    'clubs.total_members',
                    'clubs.active_members'
                )
                ->first();

            if (!$membership) {
                return response()->json(['message' => 'Aucun club trouvé pour cet utilisateur'], 403);
            }

            $this->addImageUrls($membership);

            $membersCount = DB::table('club_members')
                ->where('club_id', $membership->id)
                ->where('status', 'active')
                ->count();

            $eventsCount = DB::table('events')
                ->where('club_id', $membership->id)
                ->count();

            $pendingRequests = $membership->member_role === 'president'
                ? DB::table('request')
                    ->where('club_id', $membership->id)
                    ->where('status', 'pending')
                    ->count()
                : 0;

            return response()->json([
                'profile' => [
                    'id' => $person->id,
                    'first_name' => $person->first_name,
                    'last_name' => $person->last_name,
                    'email' => $person->email,
                    'avatar' => $person->avatar,
                    'avatar_url' => $person->avatar ? url('storage/' . $person->avatar) : null,
                ],
                'club' => $membership,
                'counts' => [
                    'members' => $membersCount,
                    'events' => $eventsCount,
                    'pending_requests' => $pendingRequests,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors du chargement du dashboard', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateMemberCounts($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_members' => 'required|integer|min:0',
            'active_members'=> 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
        }

        try {
            $club = Club::findOrFail($id);
            $club->update([
                'total_members'  => $request->total_members,
                'active_members' => $request->active_members,
            ]);
            return response()->json(['message' => 'Nombre de membres mis à jour avec succès', 'club' => $club], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur'], 500);
        }
    }
}
