<?php

namespace App\Controllers;

use App\Repositories\TemplateRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class TemplateController
{
    public function __construct(private TemplateRepository $templates) {}

    public function index(Request $r, Response $s): Response
    {
        $userId = $this->getAuthenticatedUserId($r);
        $rows = $this->templates->all($userId);

        return $this->json($s, ['count' => count($rows), 'data' => $rows]);
    }

    public function show(Request $r, Response $s, array $a): Response
    {
        $userId = $this->getAuthenticatedUserId($r);
        $template = $this->templates->find((int)$a['id']);

        if (!$template || (int)$template['user_id'] !== $userId) {
            return $this->json($s, ['error' => 'Template not found or unauthorized'], 404);
        }

        return $this->json($s, $template);
    }

    public function create(Request $r, Response $s): Response
    {
        $body = (array)$r->getParsedBody();
        $errors = $this->validate($body, true);

        if ($errors) return $this->json($s, ['errors' => $errors], 400);

        $body['user_id'] = $this->getAuthenticatedUserId($r);

        $id = $this->templates->create($body);
        $newTemplate = $this->templates->find($id);

        return $this->json($s, ['message' => 'Template created', 'data' => $newTemplate], 201)
                    ->withHeader('Location', '/api/usertemplates/' . $id);
    }

    public function update(Request $req, Response $res, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        $userId = $this->getAuthenticatedUserId($req);

        $existing = $this->templates->find($id);

        if (!$existing || (int)$existing['user_id'] !== $userId) {
            return $this->json($res, ['error' => "Template {$id} not found"], 404);
        }

        $body = (array)($req->getParsedBody() ?? []);
        $errors = $this->validate($body, false);

        if (!empty($errors)) {
            return $this->json($res, ['errors' => $errors], 400);
        }

        $this->templates->update($id, $body);
        $updatedTemplate = $this->templates->find($id);

        return $this->json($res, ['message' => 'Template updated', 'data' => $updatedTemplate]);
    }

    public function delete(Request $req, Response $res, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        $userId = $this->getAuthenticatedUserId($req);

        $existing = $this->templates->find($id);

        if (!$existing || (int)$existing['user_id'] !== $userId) {
            return $this->json($res, ['error' => "Template {$id} not found"], 404);
        }

        $this->templates->delete($id);

        return $this->json($res, ['message' => 'Template deleted', 'data' => $existing]);
    }

    private function validate(array $body, bool $requireAll): array
    {
        $errors = [];

        $rules = [
            'activity_type_id' => fn($v) => is_numeric($v) && (int)$v > 0,
            'title'            => fn($v) => is_string($v) && trim($v) !== '',
            'description'      => fn($v) => is_string($v),
            'amount'           => fn($v) => is_numeric($v) && (float)$v > 0,
        ];

        foreach ($rules as $f => $check) {
            if ($requireAll && $f !== 'description' && !array_key_exists($f, $body)) {
                $errors[$f] = "$f is required";
                continue;
            }
            if (array_key_exists($f, $body) && !$check($body[$f])) {
                $errors[$f] = "$f is invalid";
            }
        }

        return $errors;
    }

    private function getAuthenticatedUserId(Request $r): int
    {
        $auth = (array)$r->getAttribute('auth', []);
        return (int)($auth['id'] ?? 1);
    }

    private function json(Response $r, $data, int $code = 200): Response
    {
        $r->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $r->withHeader('Content-Type', 'application/json')->withStatus($code);
    }
}