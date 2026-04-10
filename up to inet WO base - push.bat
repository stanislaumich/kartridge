set /p place=< s:\place.txt
cd delphi
cd oracle
rem call backup.bat 
rem cd ..
rem cd ..
git add .
git commit -m "AUTO FROM %place% %date% %time%"
git config --global http.version HTTP/1.1
rem git push
git push origin --force
git config --global http.version HTTP/2
pause