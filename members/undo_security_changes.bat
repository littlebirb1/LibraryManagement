@echo off
echo Restoring original files...
copy /Y "delete.php.backup" "delete.php"
copy /Y "index.php.backup" "index.php"
echo Done! Original files have been restored.
pause
