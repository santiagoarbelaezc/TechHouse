# TechHouse Microservices Launcher
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "   Iniciando Microservicios TechHouse    " -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

$services = @(
    @{ Name = "Users Service";     Path = "users-service";     Port = 8001; Color = "Green" },
    @{ Name = "Products Service";  Path = "products-service";  Port = 8002; Color = "Yellow" },
    @{ Name = "Payments Service";  Path = "payments-service";  Port = 8003; Color = "Magenta" },
    @{ Name = "Assistant Service"; Path = "assistant-service"; Port = 8004; Color = "Blue" }
)

foreach ($svc in $services) {
    Write-Host "Levantando $($svc.Name) en http://localhost:$($svc.Port)..." -ForegroundColor $svc.Color
    Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PSScriptRoot\$($svc.Path)'; Write-Host 'Levantando $($svc.Name) en puerto $($svc.Port)...' -ForegroundColor $($svc.Color); php artisan serve --port=$($svc.Port)"
}

Write-Host "Todos los servicios han sido lanzados en terminales independientes." -ForegroundColor Green
Write-Host "Users:     http://localhost:8001" -ForegroundColor Gray
Write-Host "Products:  http://localhost:8002" -ForegroundColor Gray
Write-Host "Payments:  http://localhost:8003" -ForegroundColor Gray
Write-Host "Assistant: http://localhost:8004" -ForegroundColor Gray
