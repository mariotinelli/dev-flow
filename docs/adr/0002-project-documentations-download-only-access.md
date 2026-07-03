# ADR 0002: Project Documentations Download-Only Access

## Status

Accepted

## Context

ADR 0001 introduced Project Documentations with listing, creation, detail view, editing, deletion, protected download or preview, and support for file, image, and link resources.

The product direction has changed: Project Documentations should not have an internal show page or preview experience. The list is the main surface for resource access and management actions.

## Decision

Keep the three documentation resource types:

- `File`
- `Image`
- `Link`

Remove the internal Project Documentation show/detail page from the product model. The module should not expose a preview page for any resource type.

For `Link` resources:

- The primary action opens the stored external URL in a new browser tab.
- `Copy link` copies the stored external URL.
- The application does not need an internal redirect/tracking route initially.

For `File` and `Image` resources:

- The primary action downloads the resource.
- Downloads must go through an application route that authorizes access.
- The response must force download rather than preview when technically possible.
- The downloaded file should use the original uploaded filename.
- `Copy link` is not shown because temporary storage URLs expire and must not be exposed as durable resource links.

The create and edit forms must expose all business fields required by the domain:

- title
- description
- type
- category
- visibility
- URL for link resources
- file upload for file and image resources

When a user changes the documentation type, incompatible resource data must be cleared. For example, changing from `Link` to `File` clears the URL, and changing from `File` or `Image` to `Link` clears the uploaded file resource after the replacement is saved successfully.

Editing a `File` or `Image` documentation does not require re-uploading the file. A new upload replaces the previous resource. Replaced physical files should be deleted after the new resource has been stored successfully.

Platform administrators can list, open, download, edit, and delete all Project Documentations, including `AdministratorsOnly` records, independent of granular project document permissions.

Cards or rows in the list should show the primary resource action whenever the user can view the documentation. Administrative actions such as edit and delete are shown only when allowed. The UI should not display `Sem ações` when the user can still open or download the resource.

## Consequences

ADR 0001 remains valid for the core Project Documentation concept, resource types, categories, visibility model, authorization-aware file access, and replacement semantics.

This ADR supersedes ADR 0001 where it mentions detail view, preview, image preview support, or preview permission semantics.

The implementation must remove dependencies on a `show` route/page from the user-facing flow and replace them with explicit list actions.
