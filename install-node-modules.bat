@echo off
:: ============================================================
:: install-node-modules.bat
:: รัน script นี้บน Windows Server ที่มี Node.js ติดตั้งแล้ว
:: เพื่อ download และติดตั้ง JS libraries ลงใน node_modules/
:: ============================================================

echo ====================================================
echo  Installing node_modules for PRSHIS
echo ====================================================

:: เปลี่ยน directory ไปที่โฟลเดอร์ที่มี package.json
cd /d "%~dp0"

echo.
echo [1] ตรวจสอบ Node.js และ npm...
node --version
npm --version
if errorlevel 1 (
    echo ERROR: ไม่พบ Node.js กรุณาติดตั้ง Node.js ก่อน
    echo Download: https://nodejs.org/
    pause
    exit /b 1
)

echo.
echo [2] ติดตั้ง packages จาก package.json...
npm install

if errorlevel 1 (
    echo ERROR: npm install ล้มเหลว
    pause
    exit /b 1
)

echo.
echo ====================================================
echo  เสร็จสิ้น! node_modules พร้อมใช้งานแล้ว
echo ====================================================
pause
