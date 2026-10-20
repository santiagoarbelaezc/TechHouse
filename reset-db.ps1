# Script de limpieza completa y arranque limpio de bases de datos TechHouse
param (
    [switch]$Migrate
)

Write-Host "=========================================" -ForegroundColor Yellow
Write-Host "  TechHouse - Reset de Bases de Datos    " -ForegroundColor Yellow
Write-Host "=========================================" -ForegroundColor Yellow

Write-Host "1. Deteniendo y eliminando contenedores y volúmenes corruptos/anteriores..." -ForegroundColor DarkYellow
docker compose down -v

Write-Host "2. Levantando contenedores limpios..." -ForegroundColor Cyan
docker compose up -d

Write-Host "3. Esperando que las instancias de MySQL inicialicen y respondan al healthcheck..." -ForegroundColor Cyan
$maxRetries = 20
$retries = 0
$allHealthy = $false

while (-not $allHealthy -and $retries -lt $maxRetries) {
    Start-Sleep -Seconds 3
    $retries++
    
    $statuses = docker compose ps --format json | ConvertFrom-Json
    $healthyCount = 0
    foreach ($status in $statuses) {
        if ($status.Health -eq "healthy" -or $status.State -eq "running") {
            $healthyCount++
        }
    }
    
    Write-Host "   Esperando servicios de base de datos ($retries/$maxRetries)..." -ForegroundColor DarkGray
    if ($healthyCount -ge 4) {
        $allHealthy = $true
    }
}

Write-Host "-----------------------------------------" -ForegroundColor DarkGray
docker compose ps
Write-Host "-----------------------------------------" -ForegroundColor DarkGray
Write-Host "Contenedores de base de datos listos y en ejecución." -ForegroundColor Green

if ($Migrate) {
    Write-Host "Ejecutando migraciones y seeders en cada microservicio..." -ForegroundColor Magenta
    $services = @("users-service", "products-service", "payments-service", "assistant-service")
    foreach ($svc in $services) {
        Write-Host "-> Migrando $svc..." -ForegroundColor Cyan
        Push-Location "$PSScriptRoot\$svc"
        php artisan migrate:fresh --seed
        Pop-Location
    }
    Write-Host "Migraciones completadas exitosamente." -ForegroundColor Green
} else {
    Write-Host "Tip: Puedes correr `.\reset-db.ps1 -Migrate` si también deseas ejecutar las migraciones y seeders automáticamente." -ForegroundColor Gray
}
