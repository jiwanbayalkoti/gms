<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'gym_id',
        'phone',
        'profile_photo',
        'active',
        'staff_type',
        'marital_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
    ];

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Scope a query to only include users with a specific role.
     */
    public function scopeRole(Builder $query, string $role): void
    {
        $query->where('role', $role);
    }

    /**
     * Check if the user is a gym admin.
     */
    public function isGymAdmin(): bool
    {
        return $this->role === 'GymAdmin';
    }

    /**
     * Check if the user is a trainer.
     */
    public function isTrainer(): bool
    {
        return $this->role === 'Trainer';
    }

    /**
     * Check if the user is a member.
     */
    public function isMember(): bool
    {
        return $this->role === 'Member';
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'SuperAdmin';
    }

    /**
     * Check if the user is staff.
     */
    public function isStaff(): bool
    {
        return $this->role === 'Staff';
    }

    /**
     * Get the gym this user belongs to.
     * Returns null for SuperAdmin.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the effective gym_id for the user.
     * SuperAdmin returns null (can access all gyms).
     * Other users return their gym_id.
     */
    public function getEffectiveGymId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null; // SuperAdmin can access all gyms
        }
        
        return $this->gym_id;
    }

    /**
     * Check if user can access a specific gym.
     * SuperAdmin can access all gyms.
     * Other users can only access their own gym.
     */
    public function canAccessGym(?int $gymId): bool
    {
        if ($this->isSuperAdmin()) {
            return true; // SuperAdmin can access all gyms
        }
        
        return $this->gym_id === $gymId;
    }

    /**
     * Get user permissions based on role.
     */
    public function getPermissions(): array
    {
        $permissions = [];

        switch ($this->role) {
            case 'SuperAdmin':
                // SuperAdmin has all permissions - bypass checks in BaseController
                $permissions = [
                    'gyms.create',
                    'gyms.view_all',
                    'gyms.update',
                    'gyms.delete',
                    'users.create',
                    'users.view_all',
                    'users.update',
                    'users.delete',
                    'subscriptions.manage',
                    // Add all common permissions for SuperAdmin
                    'members.create',
                    'members.view',
                    'members.update',
                    'members.delete',
                    'trainers.create',
                    'trainers.view',
                    'trainers.update',
                    'trainers.delete',
                    'staff.create',
                    'staff.view',
                    'staff.update',
                    'staff.delete',
                    'classes.create',
                    'classes.view',
                    'classes.update',
                    'classes.delete',
                    'bookings.create',
                    'bookings.view',
                    'bookings.update',
                    'bookings.delete',
                    'payments.view',
                    'payments.create',
                    'attendance.view',
                    'reports.view',
                    'settings.update',
                    'membership-plans.create',
                    'membership-plans.view',
                    'membership-plans.update',
                    'membership-plans.delete',
                    'workout-plans.create',
                    'workout-plans.view',
                    'workout-plans.update',
                    'workout-plans.delete',
                    'diet-plans.create',
                    'diet-plans.view',
                    'diet-plans.update',
                    'diet-plans.delete',
                    'notifications.create',
                    'notifications.view',
                    'notifications.update',
                    'notifications.delete',
                ];
                break;

            case 'GymAdmin':
                $permissions = [
                    'members.create',
                    'members.view',
                    'members.update',
                    'members.delete',
                    'trainers.create',
                    'trainers.view',
                    'trainers.update',
                    'trainers.delete',
                    'staff.create',
                    'staff.view',
                    'staff.update',
                    'staff.delete',
                    'classes.create',
                    'classes.view',
                    'classes.update',
                    'classes.delete',
                    'bookings.create',
                    'bookings.view',
                    'bookings.update',
                    'bookings.delete',
                    'payments.view',
                    'payments.create',
                    'payments.update',
                    'payments.delete',
                    'attendance.view',
                    'reports.view',
                    'settings.update',
                    'membership-plans.create',
                    'membership-plans.view',
                    'membership-plans.update',
                    'membership-plans.delete',
                    'notifications.create',
                    'notifications.view',
                    'notifications.update',
                    'notifications.delete',
                    'events.create',
                    'events.view',
                    'events.update',
                    'events.delete',
                    'salaries.create',
                    'salaries.view',
                    'salaries.update',
                    'salaries.delete',
                    'salary-payments.create',
                    'salary-payments.view',
                    'salary-payments.update',
                ];
                break;

            case 'Trainer':
                $permissions = [
                    'workout-plans.create',
                    'workout-plans.view',
                    'workout-plans.update',
                    'diet-plans.create',
                    'diet-plans.view',
                    'diet-plans.update',
                    'classes.create',
                    'classes.view',
                    'classes.update',
                    'members.view',
                ];
                break;

            case 'Staff':
                $permissions = [
                    'bookings.view',
                    'bookings.create',
                    'attendance.view',
                    'attendance.create',
                    'members.view',
                ];
                break;

            case 'Member':
                $permissions = [
                    'profile.view',
                    'profile.update',
                    'bookings.create',
                    'bookings.view_own', // Can view own bookings
                    'attendance.view_own',
                    'payments.view_own',
                    'classes.view', // Can view assigned classes (view only, no edit/delete)
                    'workout-plans.view', // Can view assigned workout plans (view only, no edit/delete)
                    'diet-plans.view', // Can view assigned diet plans (view only, no edit/delete)
                    'events.view', // Can view published events
                ];
                break;
        }

        return $permissions;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    /**
     * Get the workout plans created by the trainer.
     */
    public function workoutPlansCreated()
    {
        return $this->hasMany(WorkoutPlan::class, 'trainer_id');
    }

    /**
     * Get the workout plans assigned to the member.
     */
    public function workoutPlansAssigned()
    {
        return $this->hasMany(WorkoutPlan::class, 'member_id');
    }

    /**
     * Get the diet plans created by the trainer.
     */
    public function dietPlansCreated()
    {
        return $this->hasMany(DietPlan::class, 'trainer_id');
    }

    /**
     * Get the diet plans assigned to the member.
     */
    public function dietPlansAssigned()
    {
        return $this->hasMany(DietPlan::class, 'member_id');
    }

    /**
     * Get the classes taught by the trainer.
     */
    public function classesTaught()
    {
        return $this->hasMany(GymClass::class, 'trainer_id');
    }

    /**
     * Get the bookings made by the member.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'member_id');
    }

    /**
     * Get the attendance records for the member.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class, 'member_id');
    }

    /**
     * Get the payments made by the member.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'member_id');
    }

    /**
     * Get the salary configuration for this employee (Trainer/Staff).
     */
    public function salary()
    {
        return $this->hasOne(Salary::class, 'employee_id');
    }

    /**
     * Get all salary payments received by this employee.
     */
    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class, 'employee_id');
    }
}
