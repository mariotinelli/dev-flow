# Context

## Glossary

### Project Documentation

Project Documentation is any material related to a project that serves as a knowledge source for the team. A documentation belongs to exactly one project and contains exactly one attached resource.

Supported resource types in the first version are file, image, and link. Other types may be added later.

### Documentation Resource

The single resource attached to a Project Documentation.

For link documentations, the resource is a valid URL stored on the documentation record.

For file and image documentations, the resource is represented by the existing `Media` entity and must not duplicate media metadata on the documentation record.

### Documentation Author

The authenticated user who creates the documentation. The author is assigned automatically and cannot be changed manually.

### Documentation Type

The kind of resource attached to a Project Documentation.

Use the `ProjectDocumentationType` enum with these values:

- `File`
- `Image`
- `Link`

### Documentation Category

The fixed business category for grouping project knowledge. It is separate from the documentation type and is required.

Use the `ProjectDocumentationCategory` enum with these values:

- `Architecture`
- `Business`
- `API`
- `Database`
- `Infrastructure`
- `Deploy`
- `Meeting`
- `Design`
- `Requirements`
- `Tutorial`
- `Other`

The default category is `Other`. Category codes are stored in English and labels are translated for display.

### Documentation Visibility

The audience allowed to see a Project Documentation.

Use the `ProjectDocumentationVisibility` enum with these values:

- `ProjectMembers`
- `AdministratorsOnly`

The default visibility is `ProjectMembers`. Only platform administrators can create or update documentation with `AdministratorsOnly` visibility.

### Platform Administrator

A user with the `admin` role. Platform administrators have full access to all Project Documentations, independent of project membership.

### Project Document Permissions

Project member access to Project Documentations is controlled by these project permissions:

- `app\Enums\Permissions\Projects\DocumentPermissions::View`
- `app\Enums\Permissions\Projects\DocumentPermissions::Create`
- `app\Enums\Permissions\Projects\DocumentPermissions::Update`
- `app\Enums\Permissions\Projects\DocumentPermissions::Delete`

`View` also grants permission to open, preview, or download the documentation resource.

## Invariants

- A Project Documentation belongs to exactly one project.
- A Project Documentation has exactly one resource.
- Link documentations have a URL and no media.
- File and image documentations have one media and no public storage URL exposed directly to users.
- The documentation author is always the authenticated creator.
- Non-administrators must never receive `AdministratorsOnly` documentations in frontend payloads.
- Project permissions do not override `AdministratorsOnly` visibility.
- Resource access must always be authorized by the application before returning a download or temporary storage URL.
