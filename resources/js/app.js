// Alpine est fourni par Livewire (@livewireScripts dans le layout).
// Les directives/composants Alpine custom s'enregistrent ici.
document.addEventListener('alpine:init', () => {
    // Copie d'un texte dans le presse-papier avec feedback "Copié !"
    window.Alpine.data('copyButton', (text) => ({
        copied: false,
        copy() {
            navigator.clipboard.writeText(text).then(() => {
                this.copied = true;
                setTimeout(() => (this.copied = false), 2000);
            });
        },
    }));
});
