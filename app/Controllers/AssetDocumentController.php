<?php

namespace App\Controllers;

use App\Models\AssetDocumentModel;
use App\Models\LabAssetModel;
use CodeIgniter\I18n\Time;
use RuntimeException;

class AssetDocumentController extends BaseController
{
    public const TYPES = ['invoice', 'bast', 'manual', 'warranty', 'photo', 'other'];

    protected AssetDocumentModel $documentModel;
    protected LabAssetModel $assetModel;

    public function __construct()
    {
        $this->documentModel = new AssetDocumentModel();
        $this->assetModel    = new LabAssetModel();
        helper('upload');
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $assetId = (int) $this->request->getGet('asset_id');

        $builder = db_connect()->table('asset_documents d')
            ->select('d.*, a.name AS asset_name, a.asset_code, u.username AS uploaded_by_name')
            ->join('lab_assets a', 'a.id = d.asset_id', 'left')
            ->join('users u', 'u.id = d.uploaded_by', 'left')
            ->orderBy('d.created_at', 'DESC')
            ->orderBy('d.id', 'DESC');

        if ($assetId > 0) {
            $builder->where('d.asset_id', $assetId);
        }

        $documents = $builder->get()->getResultArray();
        $asset     = $assetId > 0 ? $this->assetModel->find($assetId) : null;

        return $this->renderView('loans/documents/index', [
            'title'      => 'Dokumen Aset',
            'page_title' => $asset ? 'Dokumen: ' . $asset['name'] : 'Dokumen Aset',
            'documents'  => $documents,
            'asset'      => $asset,
            'assetId'    => $assetId,
            'types'      => self::TYPES,
            'assets'     => $this->assetModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function upload()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'asset_id'      => 'required|is_natural_no_zero',
            'document_type' => 'required|in_list[' . implode(',', self::TYPES) . ']',
            'title'         => 'required|max_length[150]',
            'document_file' => 'uploaded[document_file]|max_size[document_file,10240]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $assetId = (int) $this->request->getPost('asset_id');
        $asset   = $this->assetModel->find($assetId);
        if (! $asset) {
            return redirect()->back()->withInput()->with('error', 'Aset tidak ditemukan.');
        }

        try {
            $meta = handleDocumentUpload(
                $this->request->getFile('document_file'),
                'asset_documents/' . $assetId
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        if ($meta === null) {
            return redirect()->back()->withInput()->with('error', 'File gagal diunggah.');
        }

        $this->documentModel->insert([
            'asset_id'      => $assetId,
            'document_type' => $this->request->getPost('document_type'),
            'title'         => trim((string) $this->request->getPost('title')),
            'file_path'     => $meta['path'],
            'file_size'     => $meta['size'],
            'mime_type'     => $meta['mime'],
            'uploaded_by'   => auth()->id(),
            'created_at'    => Time::now()->toDateTimeString(),
        ]);

        return redirect()->to('/admin/loans/documents?asset_id=' . $assetId)
            ->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $doc = $this->documentModel->find($id);
        if (! $doc) {
            return redirect()->to('/admin/loans/documents')->with('error', 'Dokumen tidak ditemukan.');
        }

        $fullPath = WRITEPATH . $doc['file_path'];
        if (! is_file($fullPath)) {
            return redirect()->to('/admin/loans/documents?asset_id=' . (int) $doc['asset_id'])
                ->with('error', 'File fisik tidak ditemukan di server.');
        }

        return $this->response->download($fullPath, null)
            ->setFileName(basename($fullPath));
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $doc = $this->documentModel->find($id);
        if (! $doc) {
            return redirect()->to('/admin/loans/documents')->with('error', 'Dokumen tidak ditemukan.');
        }

        $fullPath = WRITEPATH . $doc['file_path'];
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $assetId = (int) $doc['asset_id'];
        $this->documentModel->delete($id);

        return redirect()->to('/admin/loans/documents?asset_id=' . $assetId)
            ->with('success', 'Dokumen dihapus.');
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.documents.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke dokumen aset.');
        }

        return null;
    }
}
