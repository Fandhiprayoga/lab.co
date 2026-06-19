<?php

namespace App\Controllers;

use App\Models\CertificateTemplateModel;
use App\Models\CertificateTemplateComponentModel;

class CertificateTemplateController extends BaseController
{
    protected CertificateTemplateModel $templateModel;
    protected CertificateTemplateComponentModel $componentModel;

    public function __construct()
    {
        $this->templateModel  = new CertificateTemplateModel();
        $this->componentModel = new CertificateTemplateComponentModel();
    }

    // ──────────────────────────────────────────────
    // Upload background ke public/uploads/ (URL-accessible)
    // ──────────────────────────────────────────────

    private function uploadBackground(): ?string
    {
        $file = $this->request->getFile('background');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, $allowedExts)) {
            throw new \RuntimeException('Tipe file tidak diizinkan. Gunakan: jpg, jpeg, png, webp.');
        }

        $maxBytes = 5 * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            throw new \RuntimeException('Ukuran file melebihi batas 5 MB.');
        }

        $dir = FCPATH . 'uploads/certificate_backgrounds';
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $newName = 'bg_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($dir, $newName);

        return 'uploads/certificate_backgrounds/' . $newName;
    }

    // ──────────────────────────────────────────────
    // Template CRUD
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $templates = $this->templateModel->orderBy('created_at', 'desc')->findAll();

        return $this->renderView('certificates/templates/index', [
            'page_title' => 'Kelola Template Sertifikat',
            'title'     => 'List Template Sertifikat',
            'templates' => $templates,
        ]);
    }

    public function create(): string
    {
        return $this->renderView('certificates/templates/create', [
            'title' => 'Buat Template Sertifikat',
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = [
            'name'             => $this->request->getPost('name'),
            'description'      => $this->request->getPost('description'),
            'page_orientation' => $this->request->getPost('page_orientation') ?: 'landscape',
            'created_by'       => user_id(),
        ];

        try {
            $data['background_path'] = $this->uploadBackground();
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $this->templateModel->insert($data);

        return redirect()->to('certificates/templates')->with('success', 'Template berhasil dibuat.');
    }

    public function edit(string $publicId): string
    {
        $template = $this->templateModel->findByPublicId($publicId);
        if (! $template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->renderView('certificates/templates/edit', [
            'title'    => 'Edit Template Sertifikat',
            'template' => $template,
        ]);
    }

    public function update(string $publicId): \CodeIgniter\HTTP\RedirectResponse
    {
        $template = $this->templateModel->findByPublicId($publicId);
        if (! $template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'name'             => $this->request->getPost('name'),
            'description'      => $this->request->getPost('description'),
            'page_orientation' => $this->request->getPost('page_orientation') ?: 'landscape',
            'updated_by'       => user_id(),
        ];

        try {
            $newBg = $this->uploadBackground();
            if ($newBg) {
                // Hapus background lama
                if ($template->background_path && file_exists(FCPATH . $template->background_path)) {
                    @unlink(FCPATH . $template->background_path);
                }
                $data['background_path'] = $newBg;
            }
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $this->templateModel->update($template->id, $data);

        return redirect()->to('certificates/templates')->with('success', 'Template berhasil diperbarui.');
    }

    public function delete(string $publicId): \CodeIgniter\HTTP\RedirectResponse
    {
        $template = $this->templateModel->findByPublicId($publicId);
        if (! $template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->templateModel->update($template->id, [
            'is_active'  => 0,
            'updated_by' => user_id(),
        ]);

        return redirect()->to('certificates/templates')->with('success', 'Template dinonaktifkan.');
    }

    // ──────────────────────────────────────────────
    // Component Management
    // ──────────────────────────────────────────────

    public function components(string $publicId): string
    {
        $template = $this->templateModel->findByPublicId($publicId);
        if (! $template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $components = $this->componentModel->getByTemplate($template->id);

        return $this->renderView('certificates/templates/components', [
            'title'      => 'Komponen: ' . $template->name,
            'template'   => $template,
            'components' => $components,
        ]);
    }

    public function storeComponent(string $publicId): \CodeIgniter\HTTP\RedirectResponse
    {
        $template = $this->templateModel->findByPublicId($publicId);
        if (! $template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->componentModel->insert([
            'template_id'    => $template->id,
            'component_type' => $this->request->getPost('component_type') ?: 'custom_text',
            'label'          => $this->request->getPost('label'),
            'content'        => $this->request->getPost('content'),
            'x_position'     => (int) $this->request->getPost('x_position') ?: 0,
            'y_position'     => (int) $this->request->getPost('y_position') ?: 0,
            'width'          => $this->request->getPost('width') ? (int) $this->request->getPost('width') : null,
            'font_size'      => (int) $this->request->getPost('font_size') ?: 16,
            'font_color'     => $this->request->getPost('font_color') ?: '#000000',
            'font_family'    => $this->request->getPost('font_family') ?: 'Arial, sans-serif',
            'font_weight'    => $this->request->getPost('font_weight') ?: 'normal',
            'text_align'     => $this->request->getPost('text_align') ?: 'center',
            'sort_order'     => (int) $this->request->getPost('sort_order') ?: 0,
        ]);

        return redirect()->back()->with('success', 'Komponen berhasil ditambahkan.');
    }

    public function updateComponent(string $publicId, int $componentId): \CodeIgniter\HTTP\RedirectResponse
    {
        $template = $this->templateModel->findByPublicId($publicId);
        if (! $template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->componentModel->update($componentId, [
            'component_type' => $this->request->getPost('component_type') ?: 'custom_text',
            'label'          => $this->request->getPost('label'),
            'content'        => $this->request->getPost('content'),
            'x_position'     => (int) $this->request->getPost('x_position') ?: 0,
            'y_position'     => (int) $this->request->getPost('y_position') ?: 0,
            'width'          => $this->request->getPost('width') ? (int) $this->request->getPost('width') : null,
            'font_size'      => (int) $this->request->getPost('font_size') ?: 16,
            'font_color'     => $this->request->getPost('font_color') ?: '#000000',
            'font_family'    => $this->request->getPost('font_family') ?: 'Arial, sans-serif',
            'font_weight'    => $this->request->getPost('font_weight') ?: 'normal',
            'text_align'     => $this->request->getPost('text_align') ?: 'center',
            'sort_order'     => (int) $this->request->getPost('sort_order') ?: 0,
        ]);

        return redirect()->back()->with('success', 'Komponen berhasil diperbarui.');
    }

    public function deleteComponent(string $publicId, int $componentId): \CodeIgniter\HTTP\RedirectResponse
    {
        $template = $this->templateModel->findByPublicId($publicId);
        if (! $template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->componentModel->delete($componentId);

        return redirect()->back()->with('success', 'Komponen dihapus.');
    }

    // ──────────────────────────────────────────────
    // AJAX: update posisi komponen (drag & drop)
    // ──────────────────────────────────────────────

    public function updateComponentAjax(string $publicId, int $componentId): \CodeIgniter\HTTP\Response
    {
        $template = $this->templateModel->findByPublicId($publicId);
        if (! $template) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Template not found'])->setStatusCode(404);
        }

        $json = $this->request->getJSON();
        if (! $json) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Invalid JSON'])->setStatusCode(400);
        }

        $update = [];
        if (isset($json->x_position)) {
            $update['x_position'] = (int) $json->x_position;
        }
        if (isset($json->y_position)) {
            $update['y_position'] = (int) $json->y_position;
        }

        if (empty($update)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No position data'])->setStatusCode(400);
        }

        $this->componentModel->update($componentId, $update);

        return $this->response->setJSON(['ok' => true]);
    }

    // ──────────────────────────────────────────────
    // Preview (dummy data)
    // ──────────────────────────────────────────────

    public function preview(string $publicId): string
    {
        $template = $this->templateModel->findByPublicId($publicId);
        if (! $template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $components = $this->componentModel->getActiveByTemplate($template->id);

        // Dummy data for preview
        $dummy = [
            'recipient_name' => 'Nama Penerima',
            'cert_number'    => 'CERT-20260618000000-ABCDEF',
            'issued_date'    => date('d F Y'),
            'title'          => 'Judul Sertifikat',
        ];

        return view('certificates/render', [
            'template'   => $template,
            'components' => $components,
            'data'       => $dummy,
            'is_preview' => true,
        ]);
    }
}
