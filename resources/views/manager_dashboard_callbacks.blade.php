@forelse ($callbacks as $callback)
    <tr data-callback-id="{{ $callback->id }}" class="callback-row">
        <td>{{ ($page_obj->currentPage() - 1) * $page_obj->perPage() + $loop->index + 1 }}</td>
        <td title="{{ $callback->customer_name ?? '' }}">
            <input type="hidden" name="added_at" class="added-at-input" value="{{ $callback->added_at->format('Y-m-d H:i:s') }}">
            <span class="display-text name-input">{{ $callback->customer_name ?? '' }}</span>
            <input type="text" class="editable-input name-input" style="display: none;" name="customer_name" maxlength="100" pattern="[A-Za-z\s]+" title="Only alphabetical characters allowed" value="{{ $callback->customer_name ?? '' }}" {{ ($user_role != 'admin' && $callback->createdBy->id != $manager->id) ? 'disabled' : '' }}>
        </td>
        <td title="{{ $callback->phone_number ?? '' }}">
            <span class="display-text phone-input">{{ $callback->phone_number ?? '' }}</span>
            <input type="text" class="editable-input phone-input" style="display: none;" name="phone_number" maxlength="20" pattern="[\+\-\(\),./#0-9\s]+" title="Only numbers, +, -, (), comma, period, /, #, and spaces allowed" value="{{ $callback->phone_number ?? '' }}" {{ ($user_role != 'admin' && $callback->createdBy->id != $manager->id) ? 'disabled' : '' }}>
        </td>
        <td title="{{ $callback->email ?? '' }}">
            <span class="display-text email-input">{{ $callback->email ?? '' }}</span>
            <input type="email" class="editable-input email-input" style="display: none;" name="email" maxlength="100" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Enter a valid email address (e.g., example@domain.com)" value="{{ $callback->email ?? '' }}" {{ ($user_role != 'admin' && $callback->createdBy->id != $manager->id) ? 'disabled' : '' }}>
        </td>
        <td title="{{ $callback->address ?? '' }}">
            <span class="display-text address-input">{{ $callback->address ?? '' }}</span>
            <textarea class="editable-input address-input" style="display: none;" name="address" rows="1" maxlength="255" {{ ($user_role != 'admin' && $callback->createdBy->id != $manager->id) ? 'disabled' : '' }}>{{ $callback->address ?? '' }}</textarea>
        </td>
        <!-- <td>
            <span class="display-text website-input">{{ $callback->website ?? '' }}</span>
            <input type="url" class="editable-input website-input" style="display: none;" name="website" maxlength="255" pattern="https?://[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(/.*)?$" title="Enter a valid URL (e.g., http://example.com)" value="{{ $callback->website ?? '' }}" {{ ($user_role != 'admin' && $callback->createdBy->id != $manager->id) ? 'disabled' : '' }}>
        </td> -->
        <td title="{{ $callback->website ?? '' }}">
            <a href="{{ $callback->website ?? '#' }}" target="_blank">
                <span class="display-text website-input">{{ $callback->website ?? '' }}</span>
            </a>
            <input type="url" class="editable-input website-input" style="display: none;" name="website" maxlength="255" pattern="https?://[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(/.*)?$" title="Enter a valid URL (e.g., http://example.com)" value="{{ $callback->website ?? '' }}" {{ ($user_role != 'admin' && $callback->createdBy->id != $manager->id) ? 'disabled' : '' }}>
        </td>

        <td title="{{ $callback->remarks ?? '' }}">
            <span class="display-text remarks-input">{{ $callback->remarks ?? '' }}</span>
            <select class="editable-input remarks-input" style="display: none;" name="remarks">
                <option value="" {{ !$callback->remarks ? 'selected' : '' }}>Select</option>
                <option value="Callback" {{ $callback->remarks == 'Callback' ? 'selected' : '' }}>Callback</option>
                <option value="Pre-sale" {{ $callback->remarks == 'Pre-sale' ? 'selected' : '' }}>Pre-sale</option>
                <option value="Sample rejected" {{ $callback->remarks == 'Sample rejected' ? 'selected' : '' }}>Sample rejected</option>
                <option value="Sale" {{ $callback->remarks == 'Sale' ? 'selected' : '' }}>Sale</option>
            </select>
        </td>
        <td title="{{ $callback->notes ?? '' }}">
            <span class="display-text notes-input">{{ $callback->notes ?? '' }}</span>
            <textarea class="editable-input notes-input" style="display: none;" name="notes" rows="1" maxlength="255">{{ $callback->notes ?? '' }}</textarea>
        </td>
        <td>
            {{ $callback->createdBy->username }}
        </td>
        <td>
            @if ($can_edit)
                <i class="fas fa-edit action-icon edit-callback" title="Edit" aria-label="Edit Callback"></i>
                <i class="fas fa-times action-icon cancel-edit" title="Cancel" style="display: none;" aria-label="Cancel Edit"></i>
                <button type="button" class="action-save-btn" style="display: none;" aria-label="Save Row">Save</button>
            @endif
            @if ($user_role == 'admin')
                <i class="fas fa-trash action-icon delete-callback" title="Delete" aria-label="Delete Callback"></i>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center">No callbacks assigned to you.</td>
    </tr>
@endforelse