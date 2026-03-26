<?php

namespace App\Services\AI\Tools;

abstract class AbstractTool implements ToolInterface
{
    public function toDefinition(): array
    {
        return [
            'name'         => $this->name(),
            'description'  => $this->description(),
            'input_schema' => $this->inputSchema(),
        ];
    }

    protected function success(array $data, ?string $summary = null): array
    {
        $result = ['status' => 'success', 'data' => $data];
        if ($summary !== null) {
            $result['summary'] = $summary;
        }

        return $result;
    }

    protected function error(string $message, string $code = 'error'): array
    {
        return [
            'status'  => 'error',
            'code'    => $code,
            'message' => $message,
        ];
    }

    protected function notFound(string $message): array
    {
        return $this->error($message, 'not_found');
    }
}
