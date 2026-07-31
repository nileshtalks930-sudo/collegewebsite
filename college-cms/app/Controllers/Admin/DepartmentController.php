<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Helpers\Uploader;
use App\Middleware\RoleMiddleware;
use App\Models\Department;

final class DepartmentController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::permission('departments.view');

        $search = trim((string) ($_GET['q'] ?? ''));

        $this->view('admin.departments.index', [
            'title' => 'Departments',
            'departments' => Department::all($search !== '' ? $search : null),
            'search' => $search,
            'success' => flash('success'),
            'error' => flash('error'),
            'canManage' => Auth::can('departments.manage'),
        ], 'admin.layouts.app');
    }

    public function create(): void
    {
        RoleMiddleware::permission('departments.manage');

        $this->view('admin.departments.form', [
            'title' => 'Add Department',
            'department' => null,
            'faculty' => [],
            'achievements' => [],
            'downloads' => [],
            'gallery' => [],
            'error' => flash('error'),
            'old' => Session::get('_old', []),
        ], 'admin.layouts.app');
    }

    public function store(): void
    {
        RoleMiddleware::permission('departments.manage');
        $this->validateCsrf();

        $data = $this->validatedDepartment();
        Session::set('_old', $this->oldFromData($data));

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('departments/create');
        }

        $headPhoto = $this->uploadHeadPhoto();
        if ($headPhoto['error'] !== null) {
            Session::flash('error', $headPhoto['error']);
            $this->redirect('departments/create');
        }
        $data['head_photo'] = $headPhoto['path'];

        $id = Department::create($data);
        $this->syncNested($id);

        Session::remove('_old');
        Session::flash('success', 'Department created successfully.');
        $this->redirect('departments/' . $id . '/edit');
    }

    public function edit(string $id): void
    {
        RoleMiddleware::permission('departments.manage');

        $department = Department::withRelations((int) $id);
        if ($department === null) {
            Session::flash('error', 'Department not found.');
            $this->redirect('departments');
        }

        $this->view('admin.departments.form', [
            'title' => 'Edit Department',
            'department' => $department,
            'faculty' => $department['faculty'],
            'achievements' => $department['achievements'],
            'downloads' => $department['downloads'],
            'gallery' => $department['gallery'],
            'error' => flash('error'),
            'success' => flash('success'),
            'old' => Session::get('_old', []),
        ], 'admin.layouts.app');
    }

    public function update(string $id): void
    {
        RoleMiddleware::permission('departments.manage');
        $this->validateCsrf();

        $deptId = (int) $id;
        $existing = Department::findById($deptId);
        if ($existing === null) {
            Session::flash('error', 'Department not found.');
            $this->redirect('departments');
        }

        $data = $this->validatedDepartment(true, $existing);
        Session::set('_old', $this->oldFromData($data));

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('departments/' . $deptId . '/edit');
        }

        $headPhoto = $existing['head_photo'] ?? null;
        if (!empty($_POST['remove_head_photo'])) {
            Uploader::deletePublic(is_string($headPhoto) ? $headPhoto : null);
            $headPhoto = null;
        }

        $upload = $this->uploadHeadPhoto();
        if ($upload['error'] !== null) {
            Session::flash('error', $upload['error']);
            $this->redirect('departments/' . $deptId . '/edit');
        }
        if (!$upload['skipped'] && $upload['path']) {
            Uploader::deletePublic(is_string($headPhoto) ? $headPhoto : null);
            $headPhoto = $upload['path'];
        }
        $data['head_photo'] = $headPhoto;

        Department::updateDepartment($deptId, $data);
        $this->syncNested($deptId);

        Session::remove('_old');
        Session::flash('success', 'Department updated successfully.');
        $this->redirect('departments/' . $deptId . '/edit');
    }

    public function destroy(string $id): void
    {
        RoleMiddleware::permission('departments.manage');
        $this->validateCsrf();

        $dept = Department::findById((int) $id);
        if ($dept === null) {
            Session::flash('error', 'Department not found.');
            $this->redirect('departments');
        }

        Department::deleteById((int) $id);
        Session::flash('success', 'Department deleted successfully.');
        $this->redirect('departments');
    }

    public function deleteDownload(string $id, string $downloadId): void
    {
        RoleMiddleware::permission('departments.manage');
        $this->validateCsrf();
        Department::deleteDownload((int) $id, (int) $downloadId);
        Session::flash('success', 'Download removed.');
        $this->redirect('departments/' . (int) $id . '/edit');
    }

    public function deleteGallery(string $id, string $imageId): void
    {
        RoleMiddleware::permission('departments.manage');
        $this->validateCsrf();
        Department::deleteGalleryImage((int) $id, (int) $imageId);
        Session::flash('success', 'Gallery image removed.');
        $this->redirect('departments/' . (int) $id . '/edit');
    }

    private function syncNested(int $departmentId): void
    {
        Department::syncFaculty($departmentId, $this->parseFaculty());
        Department::syncAchievements($departmentId, $this->parseAchievements());
        $this->storeNewDownloads($departmentId);
        $this->storeNewGallery($departmentId);
    }

    /** @return list<array<string,mixed>> */
    private function parseFaculty(): array
    {
        $rows = $_POST['faculty'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $out[] = [
                'id' => $row['id'] ?? '',
                'name' => $name,
                'designation' => trim((string) ($row['designation'] ?? '')) ?: null,
                'email' => trim((string) ($row['email'] ?? '')) ?: null,
                'phone' => trim((string) ($row['phone'] ?? '')) ?: null,
                'bio' => trim((string) ($row['bio'] ?? '')) ?: null,
                'photo' => null,
            ];
        }

        return $out;
    }

    /** @return list<array<string,mixed>> */
    private function parseAchievements(): array
    {
        $rows = $_POST['achievements'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $out[] = [
                'id' => $row['id'] ?? '',
                'title' => $title,
                'description' => trim((string) ($row['description'] ?? '')) ?: null,
                'achieved_on' => trim((string) ($row['achieved_on'] ?? '')),
            ];
        }

        return $out;
    }

    private function storeNewDownloads(int $departmentId): void
    {
        $titles = $_POST['download_title'] ?? [];
        if (!is_array($titles)) {
            $titles = [];
        }

        $files = $_FILES['download_file'] ?? [];
        $uploads = Uploader::storeMany(
            is_array($files) ? $files : [],
            'departments/downloads',
            ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip'],
            [],
            15 * 1024 * 1024
        );

        $existingCount = count(Department::downloads($departmentId));
        foreach ($uploads as $i => $upload) {
            if ($upload['skipped'] || $upload['path'] === null) {
                continue;
            }
            if ($upload['error'] !== null) {
                continue;
            }
            $title = trim((string) ($titles[$i] ?? ''));
            if ($title === '') {
                $title = 'Download ' . ($existingCount + $i + 1);
            }
            Department::addDownload($departmentId, $title, $upload['path'], $existingCount + $i);
        }
    }

    private function storeNewGallery(int $departmentId): void
    {
        $titles = $_POST['gallery_title'] ?? [];
        if (!is_array($titles)) {
            $titles = [];
        }

        $files = $_FILES['gallery_image'] ?? [];
        $uploads = Uploader::storeMany(
            is_array($files) ? $files : [],
            'departments/gallery',
            ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            5 * 1024 * 1024
        );

        $existingCount = count(Department::gallery($departmentId));
        foreach ($uploads as $i => $upload) {
            if ($upload['skipped'] || $upload['path'] === null || $upload['error'] !== null) {
                continue;
            }
            $title = trim((string) ($titles[$i] ?? '')) ?: null;
            Department::addGalleryImage($departmentId, $title, $upload['path'], $existingCount + $i);
        }
    }

    /** @return array{path:?string,error:?string,skipped:bool} */
    private function uploadHeadPhoto(): array
    {
        return Uploader::store(
            $_FILES['head_photo'] ?? [],
            'departments/heads',
            ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            5 * 1024 * 1024
        );
    }

    /**
     * @return array{
     *   name:string,slug:string,head_name:?string,head_designation:?string,head_photo:?string,
     *   description:?string,contact_email:?string,contact_phone:?string,contact_address:?string,
     *   status:int,error:?string
     * }
     */
    private function validatedDepartment(bool $isUpdate = false, ?array $existing = null): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $headName = trim((string) ($_POST['head_name'] ?? ''));
        $headDesignation = trim((string) ($_POST['head_designation'] ?? ''));
        $description = (string) ($_POST['description'] ?? '');
        $contactEmail = trim((string) ($_POST['contact_email'] ?? ''));
        $contactPhone = trim((string) ($_POST['contact_phone'] ?? ''));
        $contactAddress = trim((string) ($_POST['contact_address'] ?? ''));
        $status = isset($_POST['status']) ? 1 : 0;

        $error = null;
        $exceptId = $isUpdate && $existing ? (int) $existing['id'] : null;

        if ($name === '' || mb_strlen($name) > 200) {
            $error = 'Department name is required (max 200 characters).';
        }

        $slug = $slugInput !== '' ? Department::slugify($slugInput) : Department::slugify($name);
        if ($error === null) {
            $slug = Department::uniqueSlug($slug, $exceptId);
        }

        if ($error === null && $contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Contact email is invalid.';
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'head_name' => $headName !== '' ? $headName : null,
            'head_designation' => $headDesignation !== '' ? $headDesignation : null,
            'head_photo' => null,
            'description' => $description !== '' ? $description : null,
            'contact_email' => $contactEmail !== '' ? $contactEmail : null,
            'contact_phone' => $contactPhone !== '' ? $contactPhone : null,
            'contact_address' => $contactAddress !== '' ? $contactAddress : null,
            'status' => $status,
            'error' => $error,
        ];
    }

    /** @param array<string,mixed> $data */
    private function oldFromData(array $data): array
    {
        return [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'head_name' => $data['head_name'] ?? '',
            'head_designation' => $data['head_designation'] ?? '',
            'description' => $data['description'] ?? '',
            'contact_email' => $data['contact_email'] ?? '',
            'contact_phone' => $data['contact_phone'] ?? '',
            'contact_address' => $data['contact_address'] ?? '',
            'status' => $data['status'],
            'faculty' => $_POST['faculty'] ?? [],
            'achievements' => $_POST['achievements'] ?? [],
        ];
    }
}
