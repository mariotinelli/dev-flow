# ADR 0001: Project Documentations Module

## Status

Accepted

## Context

Projects need a documentation module where authorized users can manage knowledge materials one at a time. Each documentation may be a file, image, or link. The module must support listing, creation, detail view, editing, deletion, protected download or preview, search, filtering, and pagination.

The application already has a `Media` entity that should be reused for uploaded files and images when it provides the required metadata.

## Decision

Create a Project Documentation domain model scoped under projects at `/projects/{project}/documentations`.

Each documentation record will contain:

- `project_id`
- `author_id`
- `media_id`, nullable and used only for file/image resources
- `title`, max 255 characters
- `description`, nullable text
- `type`, using `ProjectDocumentationType`
- `category`, using `ProjectDocumentationCategory`, default `Other`
- `visibility`, using `ProjectDocumentationVisibility`, default `ProjectMembers`
- `url`, nullable and used only for link resources
- standard `created_at` and `updated_at` timestamps

Authorization will be centralized in `ProjectDocumentationPolicy`.

Platform administrators are identified by the `admin` role and have full access to all documentations, even when they are not project members.

Project members require these permissions:

- `DocumentPermissions::View` to list, view, preview, open, or download project documentations.
- `DocumentPermissions::Create` to create project documentations.
- `DocumentPermissions::Update` to edit project documentations.
- `DocumentPermissions::Delete` to delete project documentations.

`AdministratorsOnly` visibility is stricter than project permissions. Non-administrators cannot see those records in lists or payloads and cannot access them directly.

Only administrators may create or update `AdministratorsOnly` documentations or change visibility.

## Resource Rules

Each documentation has exactly one resource.

For `Link`:

- `url` is required.
- The URL must be syntactically valid.
- No `Media` record is created.
- Opening the resource opens the stored URL in a new tab.

For `File` and `Image`:

- A file upload is required when creating the documentation, changing the type to file/image, or replacing the resource.
- Existing media is preserved when updating only metadata or when no replacement upload is provided.
- The existing `Media` entity stores metadata such as MIME type, size, extension, and original name when available.
- Public storage URLs must not be exposed directly.

Images support preview in the interface. Files are always downloaded or opened according to browser behavior as downloads. SVG is not accepted initially.

Accepted image extensions:

- `jpg`
- `jpeg`
- `png`
- `webp`
- `gif`

Accepted file extensions:

- `pdf`
- `doc`
- `docx`
- `xls`
- `xlsx`
- `ppt`
- `pptx`
- `txt`
- `md`
- `zip`
- `rar`
- `7z`
- `json`
- `xml`
- `csv`

The initial upload size limit is 100 MB and may become configurable later.

## Querying Rules

The list supports:

- partial case-insensitive search by title
- filter by one type at a time
- filter by one category at a time
- administrator-only filter by visibility
- pagination with 10 records per page

Search and filters are combined with `AND` semantics.

## Deletion And Replacement

Deleting a documentation deletes the documentation record, the associated `Media` record, and the physical file when present.

When replacing a file/image or changing from file/image to link, the new resource must be saved successfully before deleting the previous media.

If physical file deletion fails, the application logs the failure, removes the database records, schedules later cleanup, and does not block the user operation.

## Access To Stored Files

Resource access always goes through an application route that authorizes the request first.

For S3, after authorization the application generates a temporary signed URL that expires after 5 minutes and redirects the user to it.

For local or test storage, the same protected route may serve the file directly.

If a documentation ID does not belong to the project in the route, it is treated as not found in that project and returns 404.

If the documentation exists in the project but the user lacks permission, the response is 403.

## Consequences

This keeps uploaded file metadata centralized in `Media` while preserving documentation-specific business concepts on the Project Documentation model.

The visibility enum gives the module room to add future visibility modes without replacing a boolean column.

All sensitive file access remains private and authorization-aware, including image previews.
