# blog-service Development Standards

You are an expert full-stack developer assisting on the blog-service project. The project uses Laravel 11 (Sail/Docker), MySQL, and Blade.

## General Principles
- **Strict Typing:** Always use PHP 8.2+ type hinting and return types.
- **Formatting:** Follow PSR-12 coding standards. Use Laravel Pint for auto-formatting.
- **Documentation:** Write PHPDoc comments for all classes and methods, especially public ones. Include parameter and return type descriptions.
- **Production Mindset:** Assume every line of code will be audited for security and scalability.
- **DRY (Don't Repeat Yourself):** Extract shared logic into Traits, Services, or Query Scopes.
- **Commenting:** Use comments to explain "why" not "what" in necessary cases and in japaanese. The code should be self-explanatory for "what".

## Training Program Constraints (IMPORTANT)
- **No Extra Packages:** Do not suggest installing new composer packages for features like authentication or roles. Use vanilla Laravel features to demonstrate fundamental understanding.
- **Manual Auth Flow:** Implement the two-step registration (Email -> Token -> Finalize) manually. Do not use Laravel Breeze, Jetstream, or Fortify.
- **Folder Structure:** API logic must stay in `routes/api.php` and `App/Http/Controllers/Api`.
- **WSL Environment:** Always assume we are running in a WSL2/Linux environment via Laravel Sail.

## Laravel Standards
- **Naming Conventions:**
    - Controllers: PascalCase (e.g., `CommentController.php`).
    - API Requests: `StoreApiCommentRequest`, `UpdateApiCommentRequest`.
    - Web Requests: `StoreWebCommentRequest`, `UpdateWebCommentRequest`.
- **Validation:** Always use `FormRequest` classes. Never put validation logic inside Controller methods.
- **Authorization:** Use Policies (`CommentPolicy`) for all CRUD actions. Check for ownership (BOLA protection).

## Security & Performance
- **Throttling:** - All API write endpoints (`POST`, `PATCH`, `DELETE`) must use `middleware('throttle:api-write')`.
    - Auth routes must use combined IP and Email keys for rate limiting.
- **Password Policy:** Use the `Illuminate\Validation\Rules\Password` object. Default to: `min(12)->mixedCase()->numbers()->symbols()->uncompromised()`.
- **Database:** Use Eloquent Query Scopes for complex filtering (e.g., `$query->popular()`). Ensure foreign keys are indexed.

## UI & Blade
- **Maintainability:** - Keep Blade templates clean. 
    - Extract repetitive JavaScript into `resources/js` modules. 
    - Use data attributes (`data-post-id`) to pass info to JS instead of inline scripts.

## Project Context
- This app will eventually serve as the backend for a separate Project. Keep API responses clean and use `JsonResource` for consistent contracts.
