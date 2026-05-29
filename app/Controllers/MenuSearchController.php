<?php

namespace App\Controllers;

use App\Config\MenuRegistry;

class MenuSearchController extends BaseController
{
    /**
     * GET /menu-search?q=...
     * Kembalikan JSON array menu yang cocok dengan query dan
     * dapat diakses oleh user yang sedang login berdasarkan permission.
     */
    public function search(): \CodeIgniter\HTTP\ResponseInterface
    {
        $q = trim((string) $this->request->getGet('q'));

        if (mb_strlen($q) < 1) {
            return $this->response->setJSON([]);
        }

        $results = [];

        foreach (MenuRegistry::all() as $item) {
            // Filter permission
            if ($item['group'] !== null) {
                if (! activeGroupIs($item['group'])) {
                    continue;
                }
            } elseif ($item['permission'] !== null) {
                if (! activeGroupCan($item['permission'])) {
                    continue;
                }
            }

            // Filter kecocokan label (case-insensitive)
            if (stripos($item['label'], $q) !== false) {
                $results[] = [
                    'label' => $item['label'],
                    'url'   => base_url($item['url']),
                    'icon'  => $item['icon'],
                ];
            }
        }

        return $this->response
            ->setContentType('application/json')
            ->setJSON($results);
    }
}
