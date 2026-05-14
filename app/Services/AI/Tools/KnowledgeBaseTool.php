<?php

namespace App\Services\AI\Tools;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KnowledgeBaseTool extends AbstractTool
{
    public function name(): string
    {
        return 'knowledge_base';
    }

    public function description(): string
    {
        return 'ค้นหาข้อมูลจากเอกสาร FAQ โบรชัวร์ คู่มือ หรือนโยบายของโครงการ '
            . 'Use this when the customer asks a question that may be answered in project documents, '
            . 'FAQ, terms and conditions, or any uploaded knowledge document.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'query' => [
                    'type'        => 'string',
                    'description' => 'The question or topic to search for in the knowledge base',
                ],
                'top_k' => [
                    'type'        => 'integer',
                    'description' => 'Number of relevant passages to return (default 5, max 10)',
                ],
            ],
            'required' => ['query'],
        ];
    }

    public function execute(array $input, int $organizationId): array
    {
        $query = trim($input['query']);
        if ($query === '') {
            return $this->error('กรุณาระบุ query', 'missing_param');
        }

        $topK = min((int) ($input['top_k'] ?? 5), 10);

        // Check if rag_documents table exists
        if (! Schema::hasTable('rag_documents')) {
            return $this->notFound('ยังไม่มีเอกสารในคลังความรู้');
        }

        // Keyword-based search: split query into words and match content
        $keywords = array_filter(explode(' ', $query), fn ($w) => mb_strlen($w) > 1);

        $dbQuery = DB::table('rag_documents')
            ->where('organization_id', $organizationId)
            ->where('is_active', true);

        foreach ($keywords as $keyword) {
            $dbQuery->where(function ($q) use ($keyword) {
                $q->where('content', 'like', '%' . $keyword . '%')
                    ->orWhere('title', 'like', '%' . $keyword . '%');
            });
        }

        $chunks = $dbQuery
            ->select(['title', 'content'])
            ->limit($topK)
            ->get();

        if ($chunks->isEmpty()) {
            return $this->notFound('ไม่พบข้อมูลที่เกี่ยวข้องกับ: ' . $query);
        }

        $passages = $chunks->map(fn ($row) => [
            'document' => $row->title ?? 'Unknown',
            'content'  => mb_substr($row->content, 0, 500),
        ])->values()->all();

        return $this->success($passages, 'พบ ' . count($passages) . ' ส่วนที่เกี่ยวข้อง');
    }
}
