<style>
    /* Apply custom background to Filament auth/simple pages */
    body.fi-body {
        background: url('/images/background_image.png') center center / cover no-repeat fixed !important;
    }

    body.fi-body::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        -webkit-backdrop-filter: blur(3px);
        backdrop-filter: blur(3px);
        z-index: 0;
        pointer-events: none;
    }

    body.fi-body > * {
        position: relative;
        z-index: 1;
    }

    /* Filament login card: make it glass-like like styles.css .login-form-container */
    body.fi-body .fi-simple-main,
    body.fi-body .fi-auth-card,
    body.fi-body .fi-section,
    body.fi-body .fi-section-content-ctn,
    body.fi-body .fi-section-content {
        background: rgba(255, 255, 255, 0.1) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        backdrop-filter: blur(20px) !important;
        border-radius: 24px !important;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }
</style>