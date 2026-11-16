<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NewsMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'page_id',
        'type',
        'path',
        'title',
        'is_featured',
        'order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * URL pública del archivo (disco externo de imágenes).
     */
    protected $appends = ['url'];

    public function getUrlAttribute(): ?string
    {
        return $this->path
            ? Storage::disk('external')->url($this->path)
            : null;
    }

    /**
     * Noticia a la que pertenece el archivo.
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Página institucional a la que pertenece el archivo.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(InstitutionalPage::class, 'page_id');
    }
}