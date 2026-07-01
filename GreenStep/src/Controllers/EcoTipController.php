<?php

namespace App\Controllers;

use App\Repositories\EcoTipsRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class EcoTipController
{
    public function __construct(private EcoTipsRepository $ecoTips) {}

    public function index(Request $r, Response $s): Response
    {
        $userId = $this->getAuthenticatedUserId($r);
        $rows = $this->ecoTips->all();

        // Map database records to the explicit attribute mapping expected by DashboardView.vue
        $data = array_map(fn($row) => [
            'tipID'    => (int)$row['id'],
            'tipTitle' => null, // Allows Vue template fallback handling
            'tipBody'  => $row['tip_text'],
        ], $rows);

        return $this->json($s, ['count' => count($data), 'data' => $data]);
    }

    public function show(Request $r, Response $s, array $a): Response
    {
        $id = (int)$a['id'];
        $tip = $this->ecoTips->find($id);

        if (!$tip) {
            return $this->json($s, ['error' => "Eco tip {$id} not found"], 404);
        }

        $formatted = [
            'tipID'    => (int)$tip['id'],
            'tipTitle' => null,
            'tipBody'  => $tip['tip_text'],
        ];

        return $this->json($s, $formatted);
    }

    private function getAuthenticatedUserId(Request $r): int
    {
        $auth = (array)$r->getAttribute('auth', []);
        return (int)($auth['id'] ?? 1);
    }

    private function json(Response $r, $data, int $status = 200): Response { 
        $r->getBody()->write(json_encode( 
            $data, 
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE 
            | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT 
        )); 
        return $r->withHeader('Content-Type','application/json; charset=utf-8') 
                ->withStatus($status); 
    } 
}