<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Mail\TicketMail;
use App\Models\Club_member;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| WEB ROUTES - All API routes with session support
|--------------------------------------------------------------------------
*/

Route::get('/force-clear', function() {
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('config:cache');
    return response()->json([
        'message' => 'Config cleared and cached',
        'new_config' => [
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
        ]
    ]);
});

Route::get('/test-email-now', function() {
    try {
        \Log::info('=== EMAIL TEST STARTING ===');
        $person = \App\Models\Person::first();
        $club = \App\Models\Club::first();
        if (!$person || !$club) return 'ERROR: No person or club in database';
        Mail::to($person->email)->send(new \App\Mail\WelcomeEmail($person, $club, 'member'));
        \Log::info('=== EMAIL SENT SUCCESSFULLY ===');
        return 'SUCCESS! Email sent to ' . $person->email;
    } catch (\Exception $e) {
        \Log::error('=== EMAIL FAILED: ' . $e->getMessage());
        return 'FAILED: ' . $e->getMessage();
    }
});

// ============================================
// PUBLIC AUTH ROUTES (no auth required)
// ============================================
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/register', [AuthController::class, 'register']);

// Google OAuth routes (need web middleware for sessions)
Route::prefix('/api/auth/google')->middleware('web')->group(function () {
    Route::get('/', [GoogleAuthController::class, 'loginRedirect'])->name('google.login');
    Route::get('/callback', [GoogleAuthController::class, 'loginCallback'])->name('google.callback');
    Route::get('/link', [GoogleAuthController::class, 'linkRedirect'])->name('google.link');
    Route::get('/link/callback', [GoogleAuthController::class, 'linkCallback'])->name('google.link.callback');
});

// Session verification (public - checks session internally)
Route::get('/api/verify-session', function (Request $request) {
    try {
        Log::info('=== VERIFY SESSION START ===', [
            'session_id' => session()->getId(),
            'auth_check' => Auth::check(),
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $person = Auth::user();

        if (!$person->is_active) {
            Auth::logout();
            return response()->json(['message' => 'Compte désactivé'], 401);
        }

        $clubRole = null;
        $clubId = null;

        if ($person->role === 'user') {
            $membership = Club_member::where('person_id', $person->id)
                ->where('status', 'active')
                ->orderByRaw("CASE 
                    WHEN role = 'president' THEN 1 
                    WHEN role = 'board' THEN 2 
                    WHEN role = 'member' THEN 3 
                    ELSE 4 
                END")
                ->first();

            if ($membership) {
                $clubRole = $membership->role;
                $clubId = $membership->club_id;
            }
        }

        return response()->json([
            'user' => [
                'id' => $person->id,
                'first_name' => $person->first_name,
                'last_name' => $person->last_name,
                'email' => $person->email,
                'avatar' => $person->avatar,
                'avatar_url' => $person->avatar ? url('storage/' . $person->avatar) : null,
                'member_code' => $person->member_code,
                'club_id' => $clubId,
            ],
            'role' => $person->role,
            'club_role' => $clubRole,
            'club_id' => $clubId
        ], 200);

    } catch (\Exception $e) {
        Log::error('Session verification error: ' . $e->getMessage());
        return response()->json(['message' => 'Erreur serveur'], 500);
    }
})->middleware('web');

// ============================================
// PUBLIC ROUTES (no auth required)
// ============================================
Route::post('/api/public/persons', [PersonController::class, 'store']);
Route::post('/api/public/members', [MemberController::class, 'store']);

Route::get('/api/clubs', [ClubController::class, 'index']);
Route::get('/api/clubs/{id}', [ClubController::class, 'show']);
Route::get('/api/clubs/code/{code}', [ClubController::class, 'showByCode']);
Route::get('/api/clubs/{id}/statistics', [ClubController::class, 'statistics']);

Route::get('/api/events/upcoming/list', [EventController::class, 'upcoming']);
Route::get('/api/events/past/completed', [EventController::class, 'pastEvents']);
Route::get('/api/events/club/{clubId}', [EventController::class, 'getByClub']);
Route::get('/api/events', [EventController::class, 'index']);
Route::get('/api/events/{id}', [EventController::class, 'show']);

Route::get('/api/members', [MemberController::class, 'index']);
Route::get('/api/members/{id}', [MemberController::class, 'show']);
Route::get('/api/clubs/{clubId}/stats', [MemberController::class, 'getClubStats']);

Route::get('/api/tickets/qr/{qrCode}', [TicketController::class, 'showByQRCode']);

// ============================================
// PROTECTED ROUTES — auth:web (session cookie)
// ============================================
Route::middleware(['auth:web'])->prefix('/api')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // ✅ Profile — utilise les cookies de session
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Google Account Management
    Route::get('/google/status', [GoogleAuthController::class, 'checkGoogleStatus']);
    Route::post('/google/unlink', [GoogleAuthController::class, 'unlinkGoogle']);

    // Clubs
    Route::get('/my-club', [ClubController::class, 'getMyClub']);
    Route::post('/clubs', [ClubController::class, 'store']);
    Route::put('/clubs/{id}', [ClubController::class, 'update']);
    Route::delete('/clubs/{id}', [ClubController::class, 'destroy']);
    Route::patch('/clubs/{id}/members/count', [ClubController::class, 'updateMemberCounts']);

    // Events
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    Route::patch('/events/{id}/status', [EventController::class, 'updateStatus']);
    Route::post('/events/{id}/recap', [EventController::class, 'addRecap']);

    // Members
    Route::post('/members', [MemberController::class, 'store']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::get('/my-club-membership', [MemberController::class, 'getMyClubMembership']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);
    Route::get('/persons/{personId}/clubs', [MemberController::class, 'getPersonClubs']);

    // Persons
    Route::get('/persons', [PersonController::class, 'index']);
    Route::post('/persons', [PersonController::class, 'store']);
    Route::get('/persons/{id}', [PersonController::class, 'show']);
    Route::put('/persons/{id}', [PersonController::class, 'update']);
    Route::delete('/persons/{id}', [PersonController::class, 'destroy']);
    Route::post('/persons/{id}/reactivate', [PersonController::class, 'reactivate']);
    Route::put('/persons/{id}/password', [PersonController::class, 'updatePassword']);
    Route::get('/persons/search/query', [PersonController::class, 'search']);
    Route::post('/me/avatar', [PersonController::class, 'updateAvatar']);

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::post('/tickets/scan-qr', [TicketController::class, 'scanByQRData']);
    Route::post('/tickets/{id}/scan', [TicketController::class, 'scan']);
    Route::post('/tickets/{id}/cancel', [TicketController::class, 'cancel']);
    Route::get('/events/{eventId}/tickets/stats', [TicketController::class, 'getEventStats']);

    // Requests
    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/{id}', [RequestController::class, 'show']);
    Route::put('/requests/{id}', [RequestController::class, 'update']);
    Route::delete('/requests/{id}', [RequestController::class, 'destroy']);
    Route::post('/requests/{id}/validate', [RequestController::class, 'validate']);
    Route::get('/clubs/{clubId}/requests/stats', [RequestController::class, 'getClubStats']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });
});

Route::get('/test-member-email', function() {
    $person = \App\Models\Person::first();
    $club = \App\Models\Club::first();
    if (!$person || !$club) return 'ERROR: No person or club found in database';
    try {
        Mail::to($person->email)->send(new \App\Mail\WelcomeEmail($person, $club, 'member'));
        return 'SUCCESS! Email sent to ' . $person->email;
    } catch (\Exception $e) {
        return 'FAILED: ' . $e->getMessage();
    }
});

Route::get('/check-mail-config', function() {
    return response()->json([
        'mailer' => config('mail.default'),
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
        'username' => config('mail.mailers.smtp.username'),
        'encryption' => config('mail.mailers.smtp.encryption'),
        'from_address' => config('mail.from.address'),
        'from_name' => config('mail.from.name'),
    ]);
});