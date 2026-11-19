<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Services\CatalogQueryService;
use Livewire\Component;

class SearchBar extends Component
{
    public string $query = '';

    public function mount(): void
    {
        $this->query = (string) request('q', '');
    }

    public function submit(): void
    {
        $q = trim($this->query);
        if ($q === '') {
            return;
        }

        $this->redirectRoute('search', ['q' => $q], navigate: true);
    }

    public function render(CatalogQueryService $catalog)
    {
        return view('livewire.catalog.search-bar', [
            'suggestions' => $catalog->searchSuggestions($this->query, 6),
        ]);
    }
}