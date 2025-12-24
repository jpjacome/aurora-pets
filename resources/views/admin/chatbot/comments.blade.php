@extends('admin.layout')

@section('title', 'Admin - Chatbot Comments')

@section('content')
<div class="admin-container">
    <div class="admin-toolbar" style="align-items: flex-start;">
        <div>
            <h1>Chatbot Admin Comments</h1>
            <p style="color:rgba(220,255,214,0.7); margin-top:0.25rem;">View, inspect and export comments added from the Test AI Bot.</p>
        </div>
        <div class="chatbot-action-buttons">
            <a href="{{ route('admin.chatbot.test') }}" class="btn-secondary" style="text-decoration:none;">
                <i class="ph ph-arrow-left"></i>
                Back to Test UI
            </a>
            <a href="{{ route('admin.chatbot.comment.index') }}" class="btn-primary" style="text-decoration:none;">
                <i class="ph ph-note"></i>
                Refresh
            </a>
        </div>
    </div>

    <div class="admin-card" style="padding:1rem; border-radius:8px;">
        <table class="admin-table" style="width:100%;">
            <thead>
                <tr>
                    <th>Comment</th>
                    <th>Author</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comments as $c)
                <tr>
                    <td>{{ Str::limit($c->comment, 140) }}</td>
                    <td>{{ $c->creator?->name ?? 'System' }}</td>
                    <td>{{ $c->created_at->format('Y-m-d H:i') }}</td>
                    <td><a href="#" class="btn-secondary" onclick="openCommentModal({{ $c->id }});return false;">View</a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding:1rem; color:rgba(220,255,214,0.6);">No comments found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination-wrapper">{{ $comments->links() }}</div>
    </div>
</div>

<!-- Modal reused from test page look & feel -->
<div id="commentManageModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:9999;">
    <div style="background:var(--color-2); padding:1rem; width:720px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.4); max-height:80vh; overflow:auto;">
        <h3 id="manageCommentTitle" style="margin-top:0; color:var(--color-3);">Comment</h3>
        <p id="manageCommentMeta" style="color:rgba(220,255,214,0.7);">&nbsp;</p>
        <div id="manageCommentBody" style="background:rgba(220,255,214,0.03); padding:0.75rem; border-radius:6px; color:var(--color-3);">&nbsp;</div>
        <h4 style="margin-top:0.75rem; color:var(--color-3);">Conversation Context</h4>
        <pre id="manageCommentContext" style="background:rgba(0,0,0,0.2); padding:0.75rem; border-radius:6px; color:#fff; white-space:pre-wrap;">&nbsp;</pre>
        <div style="display:flex; justify-content:flex-end; gap:0.5rem; margin-top:0.75rem;">
            <a id="manageCommentOpenConversation" href="#" class="btn-secondary" style="display:none;">Open Conversation</a>
            <button id="manageCommentClose" class="btn-secondary">Close</button>
        </div>
    </div>
</div>

@section('scripts')
<script>
    async function openCommentModal(id) {
        try {
            const res = await fetch(`/admin/chatbot/comment/${id}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Failed to fetch comment');
            const data = await res.json();
            document.getElementById('manageCommentTitle').textContent = `Comment #${data.id}`;
            document.getElementById('manageCommentMeta').textContent = `${data.created_by || 'Unknown'} • ${data.created_at}`;
            document.getElementById('manageCommentBody').textContent = data.comment;
            document.getElementById('manageCommentContext').textContent = JSON.stringify(data.conversation_context, null, 2) || '';
            // If conversation id exists in context, show an 'Open Conversation' link (not implemented: depends on conversation model)
            document.getElementById('manageCommentOpenConversation').style.display = 'none';
            document.getElementById('commentManageModal').style.display = 'flex';
        } catch (e) {
            alert('Failed to load comment');
            console.error(e);
        }
    }

    document.getElementById('manageCommentClose').addEventListener('click', function() {
        document.getElementById('commentManageModal').style.display = 'none';
    });
</script>
@endsection
