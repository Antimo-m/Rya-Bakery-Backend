<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminChecklistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminChecklistItemController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['position'] = ((int) AdminChecklistItem::query()
            ->whereDate('checklist_date', $data['checklist_date'])
            ->max('position')) + 1;

        AdminChecklistItem::create($data);

        return $this->dashboardRedirect($data['checklist_date'])
            ->with('success', 'Checklist aggiunta al banco.');
    }

    public function update(Request $request, AdminChecklistItem $checklistItem): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_done'] = $request->boolean('is_done');

        $checklistItem->update($data);

        return $this->dashboardRedirect($data['checklist_date'])
            ->with('success', 'Checklist aggiornata.');
    }

    public function destroy(AdminChecklistItem $checklistItem): RedirectResponse
    {
        $checklistDate = $checklistItem->checklist_date?->toDateString() ?? now()->toDateString();
        $checklistItem->delete();

        return $this->dashboardRedirect($checklistDate)
            ->with('success', 'Checklist eliminata.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'checklist_date' => ['required', 'date_format:Y-m-d'],
        ], [
            'title.required' => 'Inserisci una voce checklist.',
            'checklist_date.required' => 'Seleziona il giorno della checklist.',
        ]);
    }

    private function dashboardRedirect(string $checklistDate): RedirectResponse
    {
        return redirect()->to(
            route('admin.dashboard', ['checklist_date' => $checklistDate]).'#checklist-banco'
        );
    }
}
