# CMS API with Laravel + Sanctum + AI Integration

This is a CMS (Content Management System) API built using Laravel. It supports:
- User Authentication with Sanctum
- Role-based access control (Admin, Author)
- Category & Article CRUD
- AI-powered title summary generation (LLM integration)
- Token-based API usage (Postman ready)

---

## 🚀 Setup Instructions
## postman link - https://surajrajbhar-2750050.postman.co/workspace/chat-app~9c7fc691-481c-499b-a0d2-f4522d428171/collection/45713249-89eec193-97ab-445b-80da-85ec629afe07?action=share&source=copy-link&creator=45713249

## if any issue arrives while running the project or in postman can reach out - Thank you
### 1. Clone the Repository or use it anyway-
```bash
git clone https://github.com/your-username/laravel-cms-api.git
cd laravel-cms-api
php artisan key:generate
php artisan migrate --seed
php artisan serve

env and llm(for auto-generate)
OPENROUTER_API_KEY=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
OPENROUTER_DEFAULT_MODEL=gryphe/mythomax-l2-13b
#using
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms_api
DB_USERNAME=root
DB_PASSWORD=admin
