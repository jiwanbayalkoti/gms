@extends('layouts.guest')

@section('title', 'Welcome')

@section('content')
<div class="container-fluid p-0">
    <!-- Hero Section -->
    <section class="hero-section position-relative">
        <div class="hero-overlay"></div>
        <div class="container h-100 d-flex flex-column justify-content-center text-white">
            <div class="row">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Welcome to {{ config('app.name') }}</h1>
                    <p class="lead mb-4">Your all-in-one solution for gym management, fitness tracking, and member engagement.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4">Log In</a>
                        <a href="#features" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container py-4">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="fw-bold">Everything You Need to Manage Your Gym</h2>
                    <p class="lead text-muted">Streamline operations, enhance member experience, and grow your business</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title">Member Management</h5>
                            <p class="card-text text-muted">Efficiently handle member registrations, profiles, and subscriptions in one place.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-dumbbell fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title">Workout Programs</h5>
                            <p class="card-text text-muted">Create and assign personalized workout plans to your members with ease.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-apple-alt fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title">Nutrition Planning</h5>
                            <p class="card-text text-muted">Provide personalized diet plans and nutritional guidance to your members.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title">Class Scheduling</h5>
                            <p class="card-text text-muted">Organize and manage fitness classes with automated booking capabilities.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-chart-line fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title">Progress Tracking</h5>
                            <p class="card-text text-muted">Monitor and visualize members' fitness progress with detailed reports.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-credit-card fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title">Payment Processing</h5>
                            <p class="card-text text-muted">Handle subscriptions and payments securely with integrated billing.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container py-4 text-center">
            <h2 class="fw-bold mb-4">Ready to Transform Your Gym Management?</h2>
            <p class="lead mb-4">Join thousands of fitness businesses already using our platform.</p>
            @if(Route::has('login'))
                <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4">Get Started Today</a>
            @endif
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container py-4">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="fw-bold">Trusted by Fitness Professionals</h2>
                    <p class="lead text-muted">See what our clients have to say about our platform</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text mb-3">"This system has completely transformed how we run our gym. Managing members, classes, and payments has never been easier."</p>
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">JD</div>
                                <div>
                                    <h6 class="mb-0">John Doe</h6>
                                    <small class="text-muted">Fitness Center Owner</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text mb-3">"The workout and diet planning features have helped us provide much more personalized service to our members. They love it!"</p>
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">JS</div>
                                <div>
                                    <h6 class="mb-0">Jane Smith</h6>
                                    <small class="text-muted">Personal Trainer</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text mb-3">"Since implementing this system, we've seen a 30% increase in class attendance and membership renewals. The ROI has been amazing."</p>
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">RJ</div>
                                <div>
                                    <h6 class="mb-0">Robert Johnson</h6>
                                    <small class="text-muted">Gym Manager</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .hero-section {
        background: url('{{ asset("assets/images/gym-background.jpg") }}') no-repeat center center;
        background-size: cover;
        height: 600px;
        position: relative;
        color: white;
    }
    
    /* Fallback if image doesn't exist */
    .hero-section:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        z-index: -1;
    }
    
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }
    
    .feature-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }
    
    @media (max-width: 767.98px) {
        .hero-section {
            height: 450px;
        }
    }
</style>
@endpush 