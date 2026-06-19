<?php

namespace App\Controllers;

use App\Models\CertificateIssuanceModel;
use App\Models\CertificateTemplateModel;

class CertificateIssuanceController extends BaseController
{
    protected CertificateIssuanceModel $issuanceModel;
    protected CertificateTemplateModel $templateModel;

    public function __construct()
    {
        $this->issuanceModel = new CertificateIssuanceModel();
        $this->templateModel  = new CertificateTemplateModel();
    }

    // ──────────────────────────────────────────────
    // Index — List all issuances
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $templateId = $this->request->getGet('template_id');
        $status     = $this->request->getGet('status');

        $builder = $this->issuanceModel
            ->select('certificate_issuances.*, ct.name as template_name')
            ->join('certificate_templates ct', 'ct.id = certificate_issuances.template_id')
            ->orderBy('issued_at', 'desc');

        if ($templateId) {
            $builder->where('certificate_issuances.template_id', $templateId);
        }
        if ($status === 'revoked') {
            $builder->where('is_revoked', 1);
        } elseif ($status === 'active') {
            $builder->where('is_revoked', 0);
        }

        $issuances = $builder->findAll();
        $templates = $this->templateModel->getActive();

        return $this->renderView('certificates/issuances/index', [
            'title'      => 'Daftar Sertifikat',
            'issuances'  => $issuances,
            'templates'  => $templates,
            'filterTemplateId' => $templateId,
            'filterStatus'     => $status,
        ]);
    }

    // ──────────────────────────────────────────────
    // Create — Individual recipient form
    // ──────────────────────────────────────────────

    public function create(): string
    {
        $templates = $this->templateModel->getActive();

        // Get eligible recipients (users with mahasiswa/asisten role)
        $db = \Config\Database::connect();
        $users = $db->table('users')
            ->select('users.id, users.username, up.nim_nik')
            ->join('user_profiles up', 'up.user_id = users.id', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = users.id', 'inner')
            ->whereIn('agu.group', ['mahasiswa', 'asisten'])
            ->where('users.active', 1)
            ->groupBy('users.id')
            ->orderBy('users.username', 'asc')
            ->limit(200)
            ->get()->getResultObject();

        return $this->renderView('certificates/issuances/create', [
            'title'     => 'Terbitkan Sertifikat',
            'templates' => $templates,
            'users'     => $users,
        ]);
    }

    // ──────────────────────────────────────────────
    // Store — Process individual issuances
    // ──────────────────────────────────────────────

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $templateId    = $this->request->getPost('template_id');
        $recipientIds  = $this->request->getPost('recipient_user_id') ?: [];
        $recipientNames = $this->request->getPost('recipient_name') ?: [];
        $recipientRoles = $this->request->getPost('recipient_role') ?: [];
        $notes         = $this->request->getPost('notes');

        $template = $this->templateModel->find($templateId);
        if (! $template) {
            return redirect()->back()->withInput()->with('error', 'Template tidak ditemukan.');
        }

        if (empty($recipientIds)) {
            return redirect()->back()->withInput()->with('error', 'Pilih setidaknya satu penerima.');
        }

        $count = 0;
        foreach ($recipientIds as $idx => $userId) {
            $recipientName = $recipientNames[$idx] ?? 'Unknown';
            $recipientRole = $recipientRoles[$idx] ?? '';

            $this->issuanceModel->insert([
                'template_id'      => $template->id,
                'recipient_user_id' => (int) $userId ?: null,
                'recipient_name'   => $recipientName,
                'recipient_role'   => $recipientRole,
                'issued_by'        => user_id(),
                'issued_at'        => date('Y-m-d H:i:s'),
                'notes'            => $notes,
            ]);

            if ($userId) {
                send_notification($userId, 'certificate.issued', [
                    'template_name' => $template->name,
                    'url'           => '/my-certificates',
                ]);
            }

            $count++;
        }

        return redirect()->to('certificates/issuances')->with('success', $count . ' sertifikat berhasil diterbitkan.');
    }

    // ──────────────────────────────────────────────
    // Bulk CSV
    // ──────────────────────────────────────────────

    public function bulkCsv(): \CodeIgniter\HTTP\RedirectResponse
    {
        $templateId = $this->request->getPost('template_id');
        $notes      = $this->request->getPost('notes');

        $template = $this->templateModel->find($templateId);
        if (! $template) {
            return redirect()->back()->withInput()->with('error', 'Template tidak ditemukan.');
        }

        $file = $this->request->getFile('csv_file');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->withInput()->with('error', 'File CSV tidak valid.');
        }

        $handle = fopen($file->getTempName(), 'r');
        if (! $handle) {
            return redirect()->back()->withInput()->with('error', 'Gagal membaca file CSV.');
        }

        $header = fgetcsv($handle);
        $count  = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 1) continue;

            $recipientName = trim($row[0] ?? '');
            $recipientRole = trim($row[1] ?? '');
            $rowNotes      = trim($row[2] ?? '');

            if (empty($recipientName)) {
                $errors[] = 'Baris ' . ($count + 2) . ': nama penerima kosong, dilewati.';
                continue;
            }

            $this->issuanceModel->insert([
                'template_id'       => $template->id,
                'recipient_user_id' => null,
                'recipient_name'    => $recipientName,
                'recipient_role'    => $recipientRole,
                'issued_by'         => user_id(),
                'issued_at'         => date('Y-m-d H:i:s'),
                'notes'             => $rowNotes ?: $notes,
            ]);

            $count++;
        }

        fclose($handle);

        $message = $count . ' sertifikat berhasil diterbitkan via CSV.';
        if (! empty($errors)) {
            $message .= ' ' . count($errors) . ' baris gagal.';
            session()->setFlashdata('csv_errors', $errors);
        }

        return redirect()->to('certificates/issuances')->with('success', $message);
    }

    // ──────────────────────────────────────────────
    // Show / Detail
    // ──────────────────────────────────────────────

    public function show(string $publicId): string
    {
        $issuance = $this->issuanceModel->findByPublicId($publicId);
        if (! $issuance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $template = $this->templateModel->find($issuance->template_id);

        return $this->renderView('certificates/issuances/show', [
            'title'    => 'Detail Sertifikat',
            'issuance' => $issuance,
            'template' => $template,
        ]);
    }

    // ──────────────────────────────────────────────
    // Revoke
    // ──────────────────────────────────────────────

    public function revoke(string $publicId): \CodeIgniter\HTTP\RedirectResponse
    {
        $issuance = $this->issuanceModel->findByPublicId($publicId);
        if (! $issuance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($issuance->is_revoked) {
            return redirect()->back()->with('error', 'Sertifikat sudah dicabut sebelumnya.');
        }

        $this->issuanceModel->update($issuance->id, [
            'is_revoked'    => 1,
            'revoked_by'    => user_id(),
            'revoked_at'    => date('Y-m-d H:i:s'),
            'revoke_reason' => $this->request->getPost('revoke_reason'),
        ]);

        return redirect()->back()->with('success', 'Sertifikat ' . $issuance->cert_code . ' berhasil dicabut.');
    }

    // ──────────────────────────────────────────────
    // My Certificates — For recipient
    // ──────────────────────────────────────────────

    public function myCertificates(): string
    {
        $userId      = user_id();
        $certificates = $this->issuanceModel->getByRecipient($userId);

        return $this->renderView('certificates/my_certificates', [
            'title'        => 'Sertifikat Saya',
            'certificates' => $certificates,
        ]);
    }
}
