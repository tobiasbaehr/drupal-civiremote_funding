# File upload

The file upload makes use of Drupal's `managed_file` form element. Some
additional information to Drupal's `File` entity is added by the
`FundingFile` entity.

When a form uses such a file upload field a URI containing a token is sent to
the backend. The backend can then use this URI to fetch the file. The backend
has to reply the request with a response that contains a map from those URIs to
a corresponding backend URI.

Files on the backend are always tried to load from the backend, when accessed by
the user and stored using Drupal's file module. In this way access permissions
can always be checked by the backend. To avoid unnecessary data transfer the
backend should implement HTTP caching.

Unused files are deleted after a configurable amount of time
(`file_cleanup_delay`). In that way Drupal's file storage acts as a cache.
