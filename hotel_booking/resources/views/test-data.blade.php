<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking Platform - Database Overview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 36px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 14px;
        }
        
        .section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .section h2 {
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .property-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .property-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .property-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge.published {
            background: #10b981;
            color: white;
        }
        
        .badge.draft {
            background: #f59e0b;
            color: white;
        }
        
        .property-info {
            color: #666;
            margin-bottom: 10px;
        }
        
        .room-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .room-card {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        
        .room-name {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .price {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            margin: 10px 0;
        }
        
        .amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .amenity-tag {
            background: #667eea;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .admin-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .admin-table tr:hover {
            background: #f9f9f9;
        }
        
        .role-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .role-admin { background: #dc2626; color: white; }
        .role-manager { background: #f59e0b; color: white; }
        .role-front_desk { background: #3b82f6; color: white; }
        
        .api-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .api-link {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .api-link:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hotel Booking Platform</h1>
            <p>Complete database overview with seeded test data</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>{{ $stats['total_properties'] }}</h3>
                <p>Total Properties</p>
            </div>
            <div class="stat-card">
                <h3>{{ $stats['published_properties'] }}</h3>
                <p>Published Properties</p>
            </div>
            <div class="stat-card">
                <h3>{{ $stats['total_rooms'] }}</h3>
                <p>Room Types</p>
            </div>
            <div class="stat-card">
                <h3>{{ $stats['total_amenities'] }}</h3>
                <p>Amenities</p>
            </div>
            <div class="stat-card">
                <h3>{{ $stats['total_admins'] }}</h3>
                <p>Admin Users</p>
            </div>
        </div>

        <div class="section">
            <h2>📋 Admin Users (CMS Access)</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Password</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                    <tr>
                        <td>{{ $admin->name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td><span class="role-badge role-{{ $admin->role }}">{{ ucwords(str_replace('_', ' ', $admin->role)) }}</span></td>
                        <td><code>password</code></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2> Properties & Room Types</h2>
            @foreach($properties as $property)
            <div class="property-card">
                <div class="property-header">
                    <div class="property-name">{{ $property->name }}</div>
                    <span class="badge {{ $property->status }}">{{ strtoupper($property->status) }}</span>
                </div>
                <div class="property-info">
                    <strong> Location:</strong> {{ $property->location }}<br>
                    <strong> Address:</strong> {{ $property->address }}<br>
                    <strong>📸 Images:</strong> {{ $property->images->count() }} photos<br>
                    <strong> Created by:</strong> {{ $property->creator->name }}
                </div>
                
                <h4 style="margin-top: 20px; margin-bottom: 10px; color: #667eea;">Available Room Types:</h4>
                <div class="room-types">
                    @foreach($property->roomTypes as $room)
                    <div class="room-card">
                        <div class="room-name">{{ $room->name }}</div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 8px;">
                            👥 Max {{ $room->max_occupancy }} guests | 🏠 {{ $room->total_units }} units
                        </div>
                        <div class="price">${{ number_format($room->base_price, 2) }}</div>
                        <div style="font-size: 12px; color: #999;">per night (base price)</div>
                        
                        @if($room->ratePlans->count() > 0)
                        <div style="margin-top: 10px; padding: 8px; background: #fef3c7; border-radius: 4px; font-size: 11px;">
                            <strong>🎯 {{ $room->ratePlans->count() }} Rate Plan(s)</strong>
                        </div>
                        @endif
                        
                        @if($room->amenities->count() > 0)
                        <div class="amenities">
                            @foreach($room->amenities as $amenity)
                            <span class="amenity-tag">{{ $amenity->name }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="section">
            <h2>🔗 API Endpoints (Test)</h2>
            <p style="color: #666; margin-bottom: 15px;">Click these links to view JSON data:</p>
            <div class="api-links">
                <a href="/test/data" class="api-link" target="_blank">All Data Overview</a>
                <a href="/test/properties" class="api-link" target="_blank">All Properties (JSON)</a>
                <a href="/test/published-properties" class="api-link" target="_blank">✅ Published Properties Only</a>
            </div>
        </div>

        <div class="section">
            <h2>✨ What's Next?</h2>
            <ul style="line-height: 2; color: #666;">
                <li>✅ Database schema created (18 tables)</li>
                <li>✅ Eloquent models with relationships</li>
                <li>✅ Sample data seeded</li>
                <li>⏳ Admin authentication & API</li>
                <li>⏳ Availability & booking logic</li>
                <li>⏳ Frontend React application</li>
                <li>⏳ Stripe payment integration</li>
            </ul>
        </div>
    </div>
</body>
</html>
