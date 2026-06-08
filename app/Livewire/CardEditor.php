<?php

namespace App\Livewire;

use App\Enums\QrType;
use App\Models\Card;
use App\Services\ImageProcessor;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class CardEditor extends Component
{
    use WithFileUploads;

    public ?Card $card = null;

    public int $step = 1;

    public string $qr_type = 'none';

    public string $qr_value = '';

    public string $title = '';

    public string $subtitle = '';

    /** @var list<array{label: string, value: string}> */
    public array $fields = [];

    /** @var list<array{label: string, value: string}> */
    public array $back_fields = [];

    public string $bg_color = '#DD7FF9';

    public string $text_color = '#FFFFFF';

    public string $qr_color = '#000000';

    public ?string $image_path = null;

    public $image = null;

    /** @var list<string> */
    public array $presetColors = ['#DD7FF9', '#FF6BAA', '#3B82F6', '#10B981', '#F59E0B'];

    public function mount(?string $editToken = null): void
    {
        if ($editToken) {
            $this->card = Card::where('edit_token', $editToken)->firstOrFail();
            $this->fill([
                'qr_type' => $this->card->qr_type->value,
                'qr_value' => (string) $this->card->qr_value,
                'title' => (string) $this->card->title,
                'subtitle' => (string) $this->card->subtitle,
                'fields' => $this->card->fields ?? [],
                'back_fields' => $this->card->back_fields ?? [],
                'bg_color' => $this->card->bg_color,
                'text_color' => $this->card->text_color,
                'qr_color' => $this->card->qr_color,
                'image_path' => $this->card->image_path,
            ]);
            $this->step = 2;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'qr_type' => 'required|in:none,url,snapchat,instagram,linkedin,text',
            'qr_value' => 'nullable|string|max:2000',
            'title' => 'nullable|string|max:60',
            'subtitle' => 'nullable|string|max:60',
            'fields' => 'array|max:4',
            'fields.*.label' => 'nullable|string|max:40',
            'fields.*.value' => 'nullable|string|max:120',
            'back_fields' => 'array|max:20',
            'back_fields.*.label' => 'nullable|string|max:40',
            'back_fields.*.value' => 'nullable|string|max:1000',
            'bg_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'qr_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'image' => 'nullable|image|max:5120',
        ];
    }

    public function selectQrType(string $type): void
    {
        $this->qr_type = $type;
    }

    public function goToStep(int $step): void
    {
        if ($step === 2) {
            $this->validateOnly('qr_type');
            $this->prefillFromQr();
        }
        $this->step = $step;
    }

    /**
     * Pré-remplit titre/sous-titre depuis le QR si l'utilisateur n'a rien saisi.
     */
    private function prefillFromQr(): void
    {
        $type = QrType::from($this->qr_type);
        $handle = ltrim(trim($this->qr_value), '@');

        if ($this->title === '' && $handle !== '' && $type !== QrType::None && $type !== QrType::Url) {
            $this->title = '@'.$handle;
        }

        if ($this->subtitle === '' && $type !== QrType::None) {
            $this->subtitle = $type->label();
        }
    }

    public function addField(): void
    {
        if (count($this->fields) < 4) {
            $this->fields[] = ['label' => '', 'value' => ''];
        }
    }

    public function removeField(int $index): void
    {
        unset($this->fields[$index]);
        $this->fields = array_values($this->fields);
    }

    public function addBackField(): void
    {
        $this->back_fields[] = ['label' => '', 'value' => ''];
    }

    public function removeBackField(int $index): void
    {
        unset($this->back_fields[$index]);
        $this->back_fields = array_values($this->back_fields);
    }

    public function setColor(string $hex): void
    {
        $this->bg_color = $hex;
    }

    public function removeImage(): void
    {
        $this->image = null;
        if ($this->image_path) {
            Storage::disk('public')->delete($this->image_path);
            $this->image_path = null;
        }
    }

    public function save(ImageProcessor $images): void
    {
        $this->validate();

        if ($this->image) {
            if ($this->image_path) {
                Storage::disk('public')->delete($this->image_path);
            }
            $this->image_path = $images->storeCardImage($this->image);
            $this->image = null;
        }

        $data = [
            'qr_type' => $this->qr_type,
            'qr_value' => $this->qr_value ?: null,
            'title' => $this->title ?: null,
            'subtitle' => $this->subtitle ?: null,
            'fields' => $this->cleanFields($this->fields),
            'back_fields' => $this->cleanFields($this->back_fields),
            'bg_color' => $this->bg_color,
            'text_color' => $this->text_color,
            'qr_color' => $this->qr_color,
            'image_path' => $this->image_path,
        ];

        if ($this->card) {
            $this->card->update($data);
        } else {
            $this->card = Card::create($data);
            session()->flash('edit_token', $this->card->edit_token);
        }

        $this->redirectRoute('cards.show', ['card' => $this->card->code], navigate: true);
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return list<array{label: string, value: string}>
     */
    private function cleanFields(array $fields): array
    {
        return collect($fields)
            ->map(fn ($f) => [
                'label' => trim((string) ($f['label'] ?? '')),
                'value' => trim((string) ($f['value'] ?? '')),
            ])
            ->filter(fn ($f) => $f['label'] !== '' || $f['value'] !== '')
            ->values()
            ->all();
    }

    #[Computed]
    public function qrPreview(): ?string
    {
        return QrType::from($this->qr_type)->resolve($this->qr_value);
    }

    #[Computed]
    public function imagePreviewUrl(): ?string
    {
        if ($this->image) {
            return $this->image->temporaryUrl();
        }
        if ($this->image_path) {
            return Storage::disk('public')->url($this->image_path);
        }

        return null;
    }

    public function render()
    {
        return view('livewire.card-editor')
            ->layout('components.layouts.app', ['title' => 'Crée ta carte — WalletCard']);
    }
}
