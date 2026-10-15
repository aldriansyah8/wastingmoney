<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wasting Money - Endpoint REST API</title>
    <link rel="icon" href="data:,">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (Helper Utilities) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    <!-- Dedicated Vanilla CSS Module for API Spec -->
    <link rel="stylesheet" href="{{ asset('css/api.css') }}">
</head>
<body class="bg-white">
    <div class="api-page-wrapper">
        <main class="api-content-container">
            <div class="api-main-card">
                <div class="api-header-section">
                    <h2 class="api-heading">
                        Endpoint REST API
                    </h2>
                </div>

                <div class="api-table-wrapper">
                    <table class="api-spec-table">
                        <thead>
                            <tr>
                                <th class="col-method">Method</th>
                                <th>Endpoint</th>
                                <th class="col-access">Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $endpoints = [
                                    ['method' => 'POST', 'path' => '/api/auth/register', 'access' => 'Publik'],
                                    ['method' => 'POST', 'path' => '/api/auth/login', 'access' => 'Publik'],
                                    ['method' => 'GET', 'path' => '/api/auth/me', 'access' => 'User/Admin'],
                                    ['method' => 'POST', 'path' => '/api/submissions', 'access' => 'User'],
                                    ['method' => 'GET', 'path' => '/api/submissions/my', 'access' => 'User'],
                                    ['method' => 'GET', 'path' => '/api/items', 'access' => 'Publik'],
                                    ['method' => 'GET', 'path' => '/api/items/{slug}', 'access' => 'Publik'],
                                    ['method' => 'GET', 'path' => '/api/admin/submissions', 'access' => 'Admin'],
                                    ['method' => 'PATCH', 'path' => '/api/admin/submissions/{id}/approve', 'access' => 'Admin'],
                                    ['method' => 'PATCH', 'path' => '/api/admin/submissions/{id}/reject', 'access' => 'Admin'],
                                ];
                            @endphp

                            @foreach($endpoints as $ep)
                                <tr>
                                    <td>
                                        <span class="api-method-badge {{ strtolower($ep['method']) }}">
                                            {{ $ep['method'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <code class="api-endpoint-badge">
                                            {{ $ep['path'] }}
                                        </code>
                                    </td>
                                    <td>
                                        <span class="api-access-text">
                                            {{ $ep['access'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
