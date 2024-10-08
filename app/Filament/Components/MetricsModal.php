<?php

namespace App\Filament\Components;

use App\Models\Task;
use App\Models\Metric;
use Filament\Forms;
use Livewire\Component;
use App\Enums\TaskState;

class MetricsModal extends Component implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public Task $task;

    public $moduleWeight;
    public $input1;
    public $input2;
    public $input3;
    public $input4;
    public $matrixValues = [];

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('moduleWeight')
                ->disabled()
                ->label('Module Weight'),
            Forms\Components\Select::make('input1')
                ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                ->required(),
            Forms\Components\Select::make('input2')
                ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                ->required(),
            Forms\Components\Select::make('input3')
                ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                ->required(),
            Forms\Components\Select::make('input4')
                ->options([1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10])
                ->required(),
            Forms\Components\Grid::make(3)
                ->schema(array_map(fn ($i) =>
                    Forms\Components\Select::make("matrixValues.{$i}")
                        ->options([0 => 0, 1 => 1, 2 => 2, 3 => 3, 5 => 5])
                        ->required(),
                    range(0, 8)
                )),
        ];
    }

    public function mount(Task $task)
    {
        $this->task = $task;
        $this->moduleWeight = $task->projectModule->weight;
    }

    public function submit()
    {
        $data = $this->form->getState();

        $calculatedValue = ($data['input1'] * $data['input2'] * $data['input3']) / $data['input4'];

        $metric = new Metric([
            'module_weight' => $this->moduleWeight,
            'input1' => $data['input1'],
            'input2' => $data['input2'],
            'input3' => $data['input3'],
            'input4' => $data['input4'],
            'calculated_value' => $calculatedValue,
            'matrix_values' => $data['matrixValues'],
        ]);

        $this->task->metric()->save($metric);
        $this->task->update(['state' => TaskState::DONE]);

        $this->emit('metricAdded');
    }

    public function render()
    {
        return view('filament.components.metrics-modal');
    }
}
