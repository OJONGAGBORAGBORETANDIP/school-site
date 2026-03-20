<style>
    /* Apply custom background to Filament auth/simple pages */
    body.fi-body {
        background: url('/images/background_image.png') center center / cover no-repeat fixed !important;
    }

    body.fi-body::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 0;
        pointer-events: none;
    }

    body.fi-body > * {
        position: relative;
        z-index: 1;
    }
</style>