@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="css/admin.panel.css">

<div class="container my-4">
    <!-- Admin Panel Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1 class="h3 mb-0" style="color: var(--color-dark-purple);">Admin Dashboard</h1>
        </div>
    </div>

    <!-- Admin Tabs -->
    <ul class="nav nav-tabs mb-0" id="adminTabs" role="tablist">
    
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                Users
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="adminTabContent">

        <!-- Users Tab -->
        <div class="tab-pane fade show active" id="users" role="tabpanel">
            <div class="card shadow-sm rounded-top-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0" style="color: var(--color-dark-purple);">Manage Users</h5>
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Active</th>
                                    <th>Joined Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>johndoe</td>
                                    <td>john.doe@example.com</td>
                                    <td><span class="badge" style="background-color: var(--color-wine);">Admin</span></td>
                                    <td><span class="badge bg-success">Yes</span></td>
                                    <td>January 15, 2024</td>
                                    <td>
                                        <button class="action-btn text-danger" title="Disable Account" data-id="1" data-action="disable">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                        <button class="action-btn text-warning" title="Unpromote from Admin" data-id="1">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                        <button class="action-btn text-danger" title="Delete user" data-id="1" data-type="user">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>janesmith</td>
                                    <td>jane.smith@example.com</td>
                                    <td><span class="badge bg-secondary">User</span></td>
                                    <td><span class="badge bg-success">Yes</span></td>
                                    <td>February 20, 2024</td>
                                    <td>
                                        <button class="action-btn text-danger" title="Disable Account" data-id="2" data-action="disable">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                        <button class="action-btn text-success" title="Promote to Admin" data-id="2">
                                            <i class="fas fa-user-shield"></i>
                                        </button>
                                        <button class="action-btn text-danger" title="Delete user" data-id="2" data-type="user">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>mikebrown</td>
                                    <td>mike.brown@example.com</td>
                                    <td><span class="badge bg-secondary">User</span></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>March 10, 2024</td>
                                    <td>
                                        <button class="action-btn text-success" title="Enable Account" data-id="3" data-action="enable">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                        <button class="action-btn text-success" title="Promote to Admin" data-id="3">
                                            <i class="fas fa-user-shield"></i>
                                        </button>
                                        <button class="action-btn text-danger" title="Delete user" data-id="3" data-type="user">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>sarahjones</td>
                                    <td>sarah.jones@example.com</td>
                                    <td><span class="badge bg-secondary">User</span></td>
                                    <td><span class="badge bg-success">Yes</span></td>
                                    <td>April 5, 2024</td>
                                    <td>
                                        <button class="action-btn text-danger" title="Disable Account" data-id="4" data-action="disable">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                        <button class="action-btn text-success" title="Promote to Admin" data-id="4">
                                            <i class="fas fa-user-shield"></i>
                                        </button>
                                        <button class="action-btn text-danger" title="Delete user" data-id="4" data-type="user">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>alexwilson</td>
                                    <td>alex.wilson@example.com</td>
                                    <td><span class="badge" style="background-color: var(--color-wine);">Admin</span></td>
                                    <td><span class="badge bg-success">Yes</span></td>
                                    <td>May 12, 2024</td>
                                    <td>
                                        <button class="action-btn text-danger" title="Disable Account" data-id="5" data-action="disable">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                        <button class="action-btn text-warning" title="Unpromote from Admin" data-id="5">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                        <button class="action-btn text-danger" title="Delete user" data-id="5" data-type="user">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection