@forelse($salaries ?? [] as $salary)
    <tr id="salary-row-{{ $salary->id }}">
        <td>{{ $salary->id }}</td>
        <td>{{ $salary->employee->name ?? 'N/A' }}</td>
        <td>
            <span class="badge badge-info">{{ ucfirst($salary->salary_type) }}</span>
        </td>
        <td>
            @if($salary->base_salary)
                ${{ number_format($salary->base_salary, 2) }}
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($salary->hourly_rate)
                ${{ number_format($salary->hourly_rate, 2) }}/hr
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($salary->commission_percentage)
                {{ $salary->commission_percentage }}%
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            <span class="badge badge-secondary">{{ ucfirst($salary->payment_frequency) }}</span>
        </td>
        <td>
            @if($salary->status === 'Active')
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-secondary">Inactive</span>
            @endif
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewSalaryModal" data-salary-id="{{ $salary->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#salaryModal" data-action="edit" data-salary-id="{{ $salary->id }}">
                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                </button>
                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                    data-delete-url="{{ route('salaries.destroy', $salary->id) }}"
                    data-delete-name="Salary #{{ $salary->id }}"
                    data-delete-type="Salary"
                    data-delete-row-id="salary-row-{{ $salary->id }}">
                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-4">
            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
            <p class="text-muted">No salaries found.</p>
        </td>
    </tr>
@endforelse


