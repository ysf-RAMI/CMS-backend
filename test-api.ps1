# Test Event Registration API
# This script will login and fetch event registrations
#
# USAGE: Open a NEW PowerShell window and run:
#   cd C:\Users\i\Desktop\webDynamic\CMS-backend
#   .\test-api.ps1
#
# Make sure the Laravel server is running in another terminal with: php artisan serve

Write-Host "=== Event Registration API Test ===" -ForegroundColor Magenta
Write-Host "Testing URL: http://127.0.0.1:8000`n" -ForegroundColor Magenta

Write-Host "=== Step 1: Login ===" -ForegroundColor Green
Write-Host "Attempting to login with admin@example.com..." -ForegroundColor Yellow

$loginData = @{
    email = "admin@example.com"
    password = "password"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/login" -Method Post -Body $loginData -ContentType "application/json"
    $token = $response.access_token
    
    Write-Host "✓ Login successful!" -ForegroundColor Green
    Write-Host "Token: $($token.Substring(0, 50))..." -ForegroundColor Yellow
    
    Write-Host "`n=== Step 2: Fetch Event Registrations ===" -ForegroundColor Green
    
    $headers = @{
        Authorization = "Bearer $token"
        Accept = "application/json"
    }
    
    $registrations = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/events/register" -Method Get -Headers $headers
    
    Write-Host "✓ Successfully fetched event registrations" -ForegroundColor Green
    Write-Host "Total registrations: $($registrations.Count)" -ForegroundColor Cyan
    
    Write-Host "`n=== Event Registrations Data ===" -ForegroundColor Cyan
    $registrations | ConvertTo-Json -Depth 5
    
} catch {
    Write-Host "✗ Error occurred:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $reader.BaseStream.Position = 0
        $reader.DiscardBufferedData()
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response Body: $responseBody" -ForegroundColor Yellow
    }
}

Write-Host "`n=== Test Complete ===" -ForegroundColor Green
