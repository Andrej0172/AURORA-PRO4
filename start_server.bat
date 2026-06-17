@echo off
start "PHP Dev Server" "C:\wamp64\bin\php\php8.4.15\php.exe" -S 0.0.0.0:8093 -t "C:\PRO_p4\AURORA-PRO4\public" "C:\PRO_p4\AURORA-PRO4\public\router.php"
echo PHP Dev Server started on http://localhost:8093
pause
