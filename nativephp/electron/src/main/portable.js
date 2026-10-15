import { app, BrowserWindow } from 'electron';
import path from 'path';
import fs from 'fs';

// 1. Determine the portable base directory on the Pendrive / Flash Drive
let portableDir = process.env.PORTABLE_EXECUTABLE_DIR;

if (!portableDir && app.isPackaged) {
    // Unpacked folder running directly from pendrive
    portableDir = path.dirname(process.execPath);
}

// Allow development override or fallback for testing
if (!portableDir) {
    if (process.env.PENDRIVE_DATA_DIR) {
        portableDir = process.env.PENDRIVE_DATA_DIR;
    } else {
        // Fallback in dev: use a dedicated portable_pendrive_mock folder
        portableDir = path.join(process.cwd(), 'pendrive_mock');
    }
}

const portableDataDir = path.join(portableDir, 'data');
const canaryTokenFile = path.join(portableDataDir, '.pendrive_token');

try {
    if (!fs.existsSync(portableDataDir)) {
        fs.mkdirSync(portableDataDir, { recursive: true });
    }
    // Write fresh canary token for active session
    fs.writeFileSync(canaryTokenFile, `SESSION_ACTIVE:${Date.now()}`);
} catch (err) {
    console.error('[PORTABLE] Could not initialize pendrive data folder:', err);
}

// 2. Redirect Electron Paths to the Pendrive (ZERO-TRACE on host computer)
try {
    app.setPath('userData', portableDataDir);
    app.setPath('sessionData', path.join(portableDataDir, 'session'));
    app.setPath('logs', path.join(portableDataDir, 'logs'));
    app.setPath('crashDumps', path.join(portableDataDir, 'crashes'));
    console.log('[PORTABLE] Active! Zero-trace isolation enabled.');
    console.log('[PORTABLE] Data & Database Directory:', portableDataDir);
} catch (err) {
    console.error('[PORTABLE] Error redirecting Electron paths:', err);
}

// 3. DEAD-MAN'S SWITCH: USB Heartbeat presence monitor
// If the pendrive is unplugged, immediately terminate all windows and processes
function triggerEmergencyShutdown(reason) {
    console.error(`[DEAD-MAN SWITCH TRIGGERED] ${reason}. Exiting immediately!`);
    try {
        const windows = BrowserWindow.getAllWindows();
        windows.forEach((win) => {
            try {
                win.destroy();
            } catch (_) {}
        });
    } catch (_) {}
    
    // Force immediate exit without leaving any trace
    app.exit(0);
    process.exit(0);
}

// Monitor the pendrive every 500ms
setInterval(() => {
    try {
        if (!fs.existsSync(portableDataDir) || !fs.existsSync(canaryTokenFile)) {
            triggerEmergencyShutdown('Pendrive unplugged or disconnected');
        }
    } catch (e) {
        triggerEmergencyShutdown('Pendrive I/O connection lost');
    }
}, 500);

export { portableDir, portableDataDir };
