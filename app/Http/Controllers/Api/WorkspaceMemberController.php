<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class WorkspaceMemberController extends Controller
{
    public function index(Workspace $workspace): JsonResponse
    {
        // Ensure user is a member of this workspace
        if (!Auth::user()->workspaces()->where('workspace_id', $workspace->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $members = $workspace->users()->get()->map(function ($user) use ($workspace) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role,
            ];
        });

        $invites = $workspace->invites()->whereNull('accepted_at')->get()->map(function ($invite) {
            return [
                'id' => $invite->id,
                'email' => $invite->email,
                'token' => $invite->token,
                'invite_url' => route('invite.accept', $invite->token),
                'role' => $invite->role,
                'created_at' => $invite->created_at,
            ];
        });

        return response()->json([
            'members' => $members,
            'invites' => $invites,
        ]);
    }

    public function createInvite(Request $request, Workspace $workspace): JsonResponse
    {
        // Only owner and admins can invite
        $user = Auth::user();
        $pivot = $user->workspaces()->where('workspace_id', $workspace->id)->first()?->pivot;

        if (!$pivot || !in_array($pivot->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Only workspace owners and admins can invite members'], 403);
        }

        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'role' => ['nullable', 'in:member,admin'],
        ]);

        $invite = WorkspaceInvite::create([
            'workspace_id' => $workspace->id,
            'invited_by' => $user->id,
            'email' => $validated['email'] ?? null,
            'token' => WorkspaceInvite::generateToken(),
            'role' => $validated['role'] ?? 'member',
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'message' => 'Invite created successfully',
            'invite' => [
                'id' => $invite->id,
                'token' => $invite->token,
                'invite_url' => route('invite.accept', $invite->token),
                'email' => $invite->email,
                'role' => $invite->role,
            ],
        ]);
    }

    public function acceptInvite(string $token)
    {
        $invite = WorkspaceInvite::where('token', $token)->first();

        if (!$invite || !$invite->isValid()) {
            return redirect('/')->with('error', 'This invite link is invalid or has expired.');
        }

        // Redirect to login if not authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Please sign in to accept the workspace invite.');
        }

        $user = Auth::user();

        // Check if already a member
        if ($user->workspaces()->where('workspace_id', $invite->workspace_id)->exists()) {
            return redirect('/')->with('info', 'You are already a member of this workspace.');
        }

        $invite->accept($user);

        return redirect('/')->with('success', 'You have joined the workspace!');
    }

    public function removeMember(Request $request, Workspace $workspace, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        $pivot = $currentUser->workspaces()->where('workspace_id', $workspace->id)->first()?->pivot;

        // Only owner can remove members (or remove yourself)
        if ($currentUser->id === $user->id) {
            // User leaving workspace
            $workspace->users()->detach($user->id);
            return response()->json(['message' => 'You have left the workspace']);
        }

        if (!$pivot || $pivot->role !== 'owner') {
            return response()->json(['message' => 'Only the workspace owner can remove members'], 403);
        }

        $workspace->users()->detach($user->id);
        return response()->json(['message' => 'Member removed successfully']);
    }
}
