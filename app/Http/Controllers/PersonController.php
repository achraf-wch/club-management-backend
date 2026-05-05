<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:persons,email',
                'password' => 'required|string|min:6',
                'cne' => 'nullable|string|max:50',
                'phone' => 'nullable|string|max:20',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'cne' => $request->cne,
                'phone' => $request->phone,
            ];

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('persons/avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            $person = Person::create($data);
            
            $person->avatar_url = $person->avatar ? url('storage/' . $person->avatar) : null;

            return response()->json([
                'message' => 'Personne créée avec succès',
                'person' => $person
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating person', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $persons = Person::all();
            
            $persons->each(function($person) {
                $person->avatar_url = $person->avatar ? url('storage/' . $person->avatar) : null;
            });
            
            return response()->json($persons, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $person = Person::findOrFail($id);
            
            $person->avatar_url = $person->avatar ? url('storage/' . $person->avatar) : null;
            
            return response()->json($person, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Personne non trouvé'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $person = Person::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'email', Rule::unique('persons', 'email')->ignore($person->id)],
                'password' => 'nullable|string|min:6',
                'cne' => 'nullable|string|max:50',
                'phone' => 'nullable|string|max:20',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $data = $request->only(['first_name', 'last_name', 'email', 'cne', 'phone']);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('avatar')) {
                if ($person->avatar && Storage::disk('public')->exists($person->avatar)) {
                    Storage::disk('public')->delete($person->avatar);
                }
                $data['avatar'] = $request->file('avatar')->store('persons/avatars', 'public');
            }

            $person->update($data);
            
            $person->avatar_url = $person->avatar ? url('storage/' . $person->avatar) : null;

            return response()->json(['message' => 'Personne mise à jour', 'person' => $person], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $person = Person::findOrFail($id);
            
            if ($person->avatar && Storage::disk('public')->exists($person->avatar)) {
                Storage::disk('public')->delete($person->avatar);
            }
            
            $person->delete();
            return response()->json(['message' => 'Personne supprimée'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur'], 500);
        }
    }
    public function updateAvatar(Request $request)
    {
        try {
            $person = auth()->user();

            if (!$person) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }

            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Fichier invalide',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Supprimer l'ancien avatar
            if ($person->avatar && Storage::disk('public')->exists($person->avatar)) {
                Storage::disk('public')->delete($person->avatar);
            }

            // Stocker le nouvel avatar
            $path = $request->file('avatar')->store('persons/avatars', 'public');

            // Mettre à jour en base
            $person->avatar = $path;
            $person->save();

            return response()->json([
                'message' => 'Photo mise à jour avec succès',
                'avatar_url' => url('storage/' . $path),
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating avatar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la photo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
