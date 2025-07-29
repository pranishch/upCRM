@forelse ($all_callbacks as $callback)
    <tr data-callback-id="{{ $callback->id }}" data-row-id="{{ $callback->id }}">
        <td>{{ $callback->customer_name ?? 'N/A' }}</td>
        <td>{{ $callback->phone_number ?? 'N/A' }}</td>
        <td>{{ $callback->email ?? 'N/A' }}</td>
        <td>{{ $callback->address ?? 'N/A' }}</td>
        <td>{{ $callback->website ?? 'N/A' }}</td>
        <td>
            <span class="display-text remarks-input">{{ $callback->remarks ?? 'N/A' }}</span>
            <select class="editable-input remarks-input form-control" style="display: none;" name="remarks">
                <option value="" {{ !$callback->remarks ? 'selected' : '' }}>Select</option>
                <option value="Callback" {{ $callback->remarks == 'Callback' ? 'selected' : '' }}>Callback</option>
                <option value="Pre-sale" {{ $callback->remarks == 'Pre-sale' ? 'selected' : '' }}>Pre-sale</option>
                <option value="Sample rejected" {{ $callback->remarks == 'Sample rejected' ? 'selected' : '' }}>Sample rejected</option>
                <option value="Sale" {{ $callback->remarks == 'Sale' ? 'selected' : '' }}>Sale</option>
            </select>
        </td>
        <td>{{ $callback->notes ?? 'N/A' }}</td>
        @if ($user_role == 'admin')
            <td>
                <!-- Debug output to inspect role -->
                <span style="display: none;">DEBUG: Role={{ $callback->createdBy->userProfile->role ?? 'No Profile' }}</span>
                @if ($callback->createdBy->userProfile && $callback->createdBy->userProfile->role == 'manager')
                    <span>{{ $callback->createdBy->username ?? 'No Manager' }}</span>
                @else
                    <select class="manager-select form-select"
                            data-row-id="{{ $callback->id }}"
                            data-username="{{ $callback->customer_name ?? 'N/A' }}">
                        <option value="" {{ !$callback->manager ? 'selected' : '' }}>No Manager</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" {{ $callback->manager && $callback->manager->id == $manager->id ? 'selected' : '' }}>{{ $manager->username }}</option>
                        @endforeach
                    </select>
                @endif
            </td>
        @endif
        <td>{{ $callback->createdBy->username }}</td>
        <td class="action-buttons">
            <button class="btn btn-info btn-action edit-callback" data-callback-id="{{ $callback->id }}" title="Edit Callback">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-danger btn-action delete-callback" data-callback-id="{{ $callback->id }}" title="Delete Callback">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ $user_role == 'admin' ? 10 : 9 }}" class="text-center">No callbacks found.</td>
    </tr>
@endforelse