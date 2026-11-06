<form method="POST" action="{{ $action }}" class="da-panel p-4 col-lg-8">@csrf @if(!empty($method))@method($method)@endif
    <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title ?? '') }}" required></div>
    <div class="mb-3"><label class="form-label">Message</label><textarea name="body" class="form-control" rows="6" required>{{ old('body', $announcement->body ?? '') }}</textarea></div>
    <div class="form-check mb-3"><input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" @checked(old('is_published', $announcement->is_published ?? false))><label class="form-check-label" for="is_published">Publish to tenant portal</label></div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
