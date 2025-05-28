# PowerShell equivalent of test.sh
Write-Host "===== URL Shortener API Tests ====="
Write-Host "Running basic connectivity tests..."

# Test database connection
Write-Host "`nTesting database connection..."
$dbTest = docker exec url_shortener_app php artisan db:monitor
Write-Host $dbTest

# Test Redis connection
Write-Host "`nTesting Redis connection..."
$redisTest = docker exec url_shortener_redis redis-cli PING
Write-Host "Redis PING response: $redisTest"

# Test Laravel environment
Write-Host "`nTesting Laravel environment..."
docker exec url_shortener_app php artisan env

# Run PHPUnit tests if they exist
if (Test-Path ".\src\phpunit.xml") {
    Write-Host "`nRunning PHPUnit tests..."
    docker exec url_shortener_app php artisan test
} else {
    Write-Host "`nNo PHPUnit tests found. Skipping..."
}

Write-Host "`n===== Tests Completed =====" 