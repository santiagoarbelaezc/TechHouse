# TechHouse Microservices & Frontend Launcher
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "      Iniciando Ecosistema TechHouse     " -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

$services = @(
    @{ Name = "Users Service";     Path = "users-service";     Port = 8001; Color = "Green";   Cmd = "php artisan serve --port=8001" },
    @{ Name = "Products Service";  Path = "products-service";  Port = 8002; Color = "Yellow";  Cmd = "php artisan serve --port=8002" },
    @{ Name = "Payments Service";  Path = "payments-service";  Port = 8003; Color = "Magenta"; Cmd = "php artisan serve --port=8003" },
    @{ Name = "Assistant Service"; Path = "assistant-service"; Port = 8004; Color = "Blue";    Cmd = "php artisan serve --port=8004" },
    @{ Name = "Angular Frontend";  Path = "frontend";          Port = 4200; Color = "Cyan";    Cmd = "npm start" }
)

foreach ($svc in $services) {
    Write-Host "Levantando $($svc.Name) en http://localhost:$($svc.Port)..." -ForegroundColor $svc.Color
    Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PSScriptRoot\$($svc.Path)'; Write-Host 'Levantando $($svc.Name) en puerto $($svc.Port)...' -ForegroundColor $($svc.Color); $($svc.Cmd)"
}

Write-Host "-----------------------------------------" -ForegroundColor DarkGray
Write-Host "Todos los servicios han sido lanzados en terminales independientes:" -ForegroundColor Green
Write-Host "  Frontend:  http://localhost:4200" -ForegroundColor Cyan
Write-Host "  Users:     http://localhost:8001" -ForegroundColor Green
Write-Host "  Products:  http://localhost:8002" -ForegroundColor Yellow
Write-Host "  Payments:  http://localhost:8003" -ForegroundColor Magenta
Write-Host "  Assistant: http://localhost:8004" -ForegroundColor Blue
Write-Host "=========================================" -ForegroundColor Cyan
