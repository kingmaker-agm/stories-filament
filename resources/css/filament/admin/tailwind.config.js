import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',

        // Awcodes Overlook plugin
        './vendor/awcodes/overlook/resources/views/**/*.blade.php',

        // Kingmake FlexLayout plugin
        './vendor/kingmaker/filament-flex-layout/resources/views/**/*.blade.php',
    ],
    safelist: [
        { pattern: /^gap-/ },
    ],
}
