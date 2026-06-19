<?php

namespace App\Controllers;

use App\Models\CertificateIssuanceModel;
use App\Models\CertificateTemplateModel;
use App\Models\CertificateTemplateComponentModel;

class CertificatePublicController extends BaseController
{
    protected CertificateIssuanceModel $issuanceModel;
    protected CertificateTemplateModel $templateModel;
    protected CertificateTemplateComponentModel $componentModel;

    public function __construct()
    {
        $this->issuanceModel  = new CertificateIssuanceModel();
        $this->templateModel   = new CertificateTemplateModel();
        $this->componentModel  = new CertificateTemplateComponentModel();
    }

    // ──────────────────────────────────────────────
    // Render certificate (auth required)
    // ──────────────────────────────────────────────

    public function render(string $certCode): string
    {
        $issuance = $this->issuanceModel->findByCertCode($certCode);
        if (! $issuance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Guard: must be logged in (recipient or admin)
        if (! auth()->loggedIn()) {
            return redirect()->route('login')->with('error', 'Login diperlukan untuk melihat sertifikat.');
        }

        $template   = $this->templateModel->find($issuance->template_id);
        $components = $this->componentModel->getActiveByTemplate($issuance->template_id);

        $data = [
            'recipient_name' => $issuance->recipient_name,
            'cert_number'    => $issuance->cert_code,
            'issued_date'    => date('d F Y', strtotime($issuance->issued_at)),
        ];

        return view('certificates/render', [
            'template'   => $template,
            'components' => $components,
            'data'       => $data,
            'is_preview' => false,
        ]);
    }

    // ──────────────────────────────────────────────
    // Public verify (no auth)
    // ──────────────────────────────────────────────

    public function verify(string $certCode): string
    {
        $issuance = $this->issuanceModel->findByCertCode($certCode);
        if (! $issuance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = \Config\Database::connect();
        $issuer = $db->table('users')
            ->select('username')
            ->where('id', $issuance->issued_by)
            ->get()->getRow();

        return view('certificates/verify', [
            'issuance'     => $issuance,
            'issuer_name'  => $issuer->username ?? 'Unknown',
        ], [
            'cache' => 300, // 5 min cache
        ]);
    }
}
