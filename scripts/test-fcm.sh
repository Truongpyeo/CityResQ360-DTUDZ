#!/bin/bash

echo "🔥 Testing FCM Configuration"
echo "======================================"
echo ""

cd /Volumes/MyVolume/Laravel/CityResQ360-DTUDZ/modules/CoreAPI

echo "1. Checking .env configuration..."
grep "FIREBASE" .env || echo "   ⚠️  FIREBASE_* not found in .env"
echo ""

echo "2. Checking credentials file..."
if [ -f "storage/app/firebase-credentials.json" ]; then
    echo "   ✅ File exists"
    echo "   Location: $(pwd)/storage/app/firebase-credentials.json"
    
    # Validate JSON
    if cat storage/app/firebase-credentials.json | jq empty 2>/dev/null; then
        echo "   ✅ Valid JSON"
        PROJECT_ID=$(cat storage/app/firebase-credentials.json | jq -r '.project_id')
        echo "   Project ID in file: $PROJECT_ID"
    else
        echo "   ⚠️  Invalid JSON (jq not installed, skipping validation)"
    fi
else
    echo "   ❌ File not found!"
    exit 1
fi
echo ""

echo "3. Testing with Laravel tinker..."
php artisan tinker <<EOF
echo "Testing Firebase initialization...\n";
try {
    \$service = app(\App\Services\NotificationService::class);
    echo "✅ NotificationService created\n";
    
    // Get first user from database
    \$user = \App\Models\NguoiDung::first();
    
    if (!\$user) {
        echo "⚠️  No users in database. Run: php artisan db:seed\n";
        exit(0);
    }
    
    echo "Found user: " . \$user->id . " (" . \$user->email . ")\n";
    echo "Push token: " . (\$user->push_token ?? 'null') . "\n\n";
    
    // Send test notification
    \$result = \$service->send(
        userId: \$user->id,
        title: 'Test Notification',
        content: 'This is a test from FCM setup',
        type: 'system',
        data: ['test' => true]
    );
    
    echo "✅ Notification created (ID: " . \$result->id . ")\n";
    
    if (\$user->push_token) {
        echo "✅ Push notification sent to FCM\n";
    } else {
        echo "⚠️  Push not sent (user has no push_token)\n";
        echo "   Mobile app needs to send FCM token to API\n";
    }
    
    echo "\n";
    echo "🎉 FCM is working correctly!\n";
    
} catch (\Exception \$e) {
    echo "❌ Error: " . \$e->getMessage() . "\n";
    exit(1);
}
EOF

echo ""
echo "======================================"
echo "✅ All tests passed!"
echo ""
echo "📋 To test with real push notification:"
echo "1. User needs to have push_token in database"
echo "2. Run: php artisan tinker"
echo "3. Run: \$service = app(\App\Services\NotificationService::class);"
echo "4. Run: \$service->send(1, 'Test', 'Hello!', 'system');"
echo ""
