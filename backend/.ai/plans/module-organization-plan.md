# Architecture Plan: User Profile & App Configuration

> Created: 2026-03-22
> Status: Planning (not yet implemented)

This document outlines the module organization for user profile, app configuration, and core features of the Trello-like board application.

---

## Architecture: Layered by Domain

Organize by layer first, then by entity/use case within each layer.

```
modules/
├── Domain/                                      # Domain Layer (Pure PHP)
│   ├── Exceptions/
│   │   ├── DomainException.php
│   │   └── ValidationDomainException.php
│   │
│   ├── User/
│   │   └── User.php                             # Existing
│   │
│   ├── Workspace/                               # New
│   │   ├── Workspace.php
│   │   └── WorkspaceMember.php
│   │
│   ├── Board/                                   # New
│   │   ├── Board.php
│   │   ├── BoardList.php
│   │   └── Card.php
│   │
│   ├── Activity/                                # New
│   │   └── ActivityLog.php
│   │
│   └── ValueObjects/
│       ├── Email.php                            # Existing
│       ├── HashedPassword.php                   # Existing
│       ├── Username.php                         # Existing
│       ├── UserFullName.php                     # Existing
│       ├── Locale.php                           # New
│       ├── BoardName.php                        # New
│       ├── CardTitle.php                        # New
│       ├── CardDescription.php                  # New
│       ├── Position.php                         # New
│       └── ...
│
├── Application/                                 # Application Layer
│   ├── Exceptions/
│   │   ├── ApplicationException.php
│   │   └── ...
│   │
│   └── UseCases/
│       ├── Auth/                                # Existing
│       │   ├── RegisterUser/
│       │   │   ├── RegisterUserHandler.php
│       │   │   ├── RegisterUserRequestDto.php
│       │   │   ├── RegisterUserResponseDto.php
│       │   │   ├── RegisterUserRepositoryInterface.php
│       │   │   └── Exceptions/
│       │   ├── LoginUser/
│       │   └── LogoutUser/
│       │
│       ├── Member/                              # Existing + New
│       │   │
│       │   │  # Profile
│       │   ├── GetMember/                       # Existing
│       │   ├── UpdateProfile/                   # New
│       │   ├── UpdateAvatar/                    # New
│       │   │
│       │   │  # Email
│       │   ├── ChangeEmail/                     # New
│       │   ├── VerifyEmail/                     # New
│       │   │
│       │   │  # Security
│       │   ├── ChangePassword/                  # New
│       │   ├── GetDevices/                      # New
│       │   ├── RevokeDevice/                    # New
│       │   ├── EnableMfa/                       # New (deferred)
│       │   ├── DisableMfa/                      # New (deferred)
│       │   │
│       │   │  # Preferences
│       │   ├── GetPreferences/                  # New
│       │   ├── UpdatePreferences/               # New
│       │   ├── GetPrivacySettings/              # New
│       │   ├── UpdatePrivacySettings/           # New
│       │   │
│       │   │  # Account
│       │   └── DeleteAccount/                   # New
│       │
│       ├── Notification/                        # New
│       │   ├── GetNotificationPreferences/
│       │   └── UpdateNotificationPreferences/
│       │
│       ├── Workspace/                           # New
│       │   ├── CreateWorkspace/
│       │   ├── GetWorkspace/
│       │   ├── InviteMember/
│       │   ├── RemoveMember/
│       │   └── UpdateMemberRole/
│       │
│       ├── Board/                               # New
│       │   ├── CreateBoard/
│       │   ├── GetBoard/
│       │   ├── UpdateBoard/
│       │   ├── ArchiveBoard/
│       │   ├── CreateList/
│       │   ├── ReorderList/
│       │   ├── CreateCard/
│       │   ├── MoveCard/
│       │   └── UpdateCard/
│       │
│       └── Activity/                            # New
│           ├── GetActivityFeed/
│           ├── GetCardActivity/
│           └── GetBoardActivity/
│
└── Infrastructure/                              # Infrastructure Layer (Laravel)
    ├── Http/
    │   ├── Controllers/
    │   │   ├── BaseController.php               # Existing
    │   │   │
    │   │   ├── Auth/                            # Existing
    │   │   │   ├── RegisterUserController.php
    │   │   │   ├── LoginUserController.php
    │   │   │   └── LogoutUserController.php
    │   │   │
    │   │   ├── Member/                          # Existing + New
    │   │   │   ├── GetMemberController.php       # Existing
    │   │   │   ├── UpdateProfileController.php   # New
    │   │   │   ├── UpdateAvatarController.php    # New
    │   │   │   ├── ChangeEmailController.php     # New
    │   │   │   ├── VerifyEmailController.php     # New
    │   │   │   ├── ChangePasswordController.php  # New
    │   │   │   ├── DevicesController.php         # New
    │   │   │   ├── PreferencesController.php     # New
    │   │   │   ├── PrivacySettingsController.php # New
    │   │   │   └── DeleteAccountController.php   # New
    │   │   │
    │   │   ├── Notification/                   # New
    │   │   │   └── NotificationPreferencesController.php
    │   │   │
    │   │   ├── Workspace/                      # New
    │   │   │   ├── WorkspaceController.php
    │   │   │   └── WorkspaceMembersController.php
    │   │   │
    │   │   ├── Board/                          # New
    │   │   │   ├── BoardController.php
    │   │   │   ├── ListController.php
    │   │   │   └── CardController.php
    │   │   │
    │   │   └── Activity/                       # New
    │   │       └── ActivityController.php
    │   │
    │   ├── Requests/
    │   │   ├── Auth/                           # Existing
    │   │   ├── Member/                         # New
    │   │   │   ├── UpdateProfileRequest.php
    │   │   │   ├── ChangeEmailRequest.php
    │   │   │   ├── ChangePasswordRequest.php
    │   │   │   └── ...
    │   │   ├── Workspace/                      # New
    │   │   ├── Board/                          # New
    │   │   └── ...
    │   │
    │   ├── Resources/                          # API Resources
    │   │   ├── MemberResource.php              # Existing
    │   │   ├── WorkspaceResource.php           # New
    │   │   ├── BoardResource.php               # New
    │   │   └── ...
    │   │
    │   └── Traits/
    │       └── ApiResponses.php                # Existing
    │
    ├── Persistence/
    │   ├── Models/
    │   │   ├── UserModel.php                   # Existing
    │   │   ├── UserPreferencesModel.php         # New
    │   │   ├── UserDeviceModel.php             # New
    │   │   ├── WorkspaceModel.php              # New
    │   │   ├── WorkspaceMemberModel.php        # New
    │   │   ├── BoardModel.php                  # New
    │   │   ├── BoardListModel.php              # New
    │   │   ├── CardModel.php                   # New
    │   │   ├── ActivityLogModel.php            # New
    │   │   └── NotificationPreferenceModel.php # New
    │   │
    │   ├── Mappers/
    │   │   ├── UserMapper.php                  # Existing
    │   │   ├── WorkspaceMapper.php             # New
    │   │   ├── BoardMapper.php                 # New
    │   │   └── ...
    │   │
    │   └── Repositories/
    │       ├── RegisterUserRepository.php      # Existing
    │       ├── LoginUserRepository.php         # Existing
    │       ├── GetMemberRepository.php         # Existing
    │       ├── UpdateProfileRepository.php     # New
    │       ├── ChangeEmailRepository.php      # New
    │       └── ...
    │
    └── Providers/
        └── RepositoryServiceProvider.php       # Existing
```

---

## Domain Entities

### User (Existing - `modules/Domain/User/User.php`)
```php
final class User
{
    private function __construct(
        private readonly string $id,
        private UserFullName $firstName,
        private UserFullName $lastName,
        private Email $email,
        private Username $username,
        private HashedPassword $password,
        private ?string $picture,
        private ?string $bio,
        private bool $emailVerified,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $deletedAt,
    ) {}
}
```

### Workspace (New - `modules/Domain/Workspace/Workspace.php`)
```php
final class Workspace
{
    private function __construct(
        private readonly string $id,
        private readonly string $ownerId,
        private WorkspaceName $name,
        private ?string $description,
        private WorkspaceVisibility $visibility,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}
}
```

### Board (New - `modules/Domain/Board/Board.php`)
```php
final class Board
{
    private function __construct(
        private readonly string $id,
        private readonly string $workspaceId,
        private readonly string $createdBy,
        private BoardName $name,
        private ?string $description,
        private bool $isArchived,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}
}
```

### BoardList (New - `modules/Domain/Board/BoardList.php`)
```php
final class BoardList
{
    private function __construct(
        private readonly string $id,
        private readonly string $boardId,
        private string $name,
        private Position $position,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}
}
```

### Card (New - `modules/Domain/Board/Card.php`)
```php
final class Card
{
    private function __construct(
        private readonly string $id,
        private readonly string $listId,
        private readonly string $boardId,
        private CardTitle $title,
        private ?CardDescription $description,
        private Position $position,
        private ?DateTimeImmutable $dueDate,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}
}
```

### ActivityLog (New - `modules/Domain/Activity/ActivityLog.php`)
```php
final class ActivityLog
{
    private function __construct(
        private readonly string $id,
        private readonly string $userId,
        private readonly ?string $workspaceId,
        private readonly ?string $boardId,
        private readonly ?string $cardId,
        private ActivityType $type,
        private ActivityMetadata $metadata,
        private readonly DateTimeImmutable $createdAt,
    ) {}
}
```

---

## Use Case Organization

### Member Use Cases (Profile, Security, Preferences, Account)

| Use Case | Group | Description |
|----------|-------|-------------|
| `GetMember` | Profile | Get authenticated user's profile |
| `UpdateProfile` | Profile | Update names, username, bio |
| `UpdateAvatar` | Profile | Update profile picture |
| `ChangeEmail` | Email | Request email change |
| `VerifyEmail` | Email | Verify new email |
| `ChangePassword` | Security | Update password |
| `GetDevices` | Security | List active sessions |
| `RevokeDevice` | Security | Revoke session |
| `EnableMfa` | Security | Enable MFA (deferred) |
| `DisableMfa` | Security | Disable MFA (deferred) |
| `GetPreferences` | Preferences | Get user preferences |
| `UpdatePreferences` | Preferences | Update preferences |
| `GetPrivacySettings` | Preferences | Get privacy settings |
| `UpdatePrivacySettings` | Preferences | Update privacy settings |
| `DeleteAccount` | Account | Soft delete account |

### Workspace Use Cases

| Use Case | Description |
|----------|-------------|
| `CreateWorkspace` | Create workspace |
| `GetWorkspace` | Get workspace details |
| `InviteMember` | Invite user to workspace |
| `RemoveMember` | Remove member from workspace |
| `UpdateMemberRole` | Change member role |

### Board Use Cases

| Use Case | Description |
|----------|-------------|
| `CreateBoard` | Create board in workspace |
| `GetBoard` | Get board with lists/cards |
| `UpdateBoard` | Update board properties |
| `ArchiveBoard` | Archive board |
| `CreateList` | Create list in board |
| `ReorderList` | Change list position |
| `CreateCard` | Create card in list |
| `MoveCard` | Move card between lists |
| `UpdateCard` | Update card details |

### Activity Use Cases

| Use Case | Description |
|----------|-------------|
| `GetActivityFeed` | Get paginated activity for user |
| `GetCardActivity` | Get activity for specific card |
| `GetBoardActivity` | Get activity for board |

---

## Database Tables

### Existing (Modified)
```sql
-- Add email_verified_at column to users
ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL;
```

### New Tables

**User Preferences:**
```sql
CREATE TABLE user_preferences (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL UNIQUE,
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    theme VARCHAR(20) DEFAULT 'system',
    notification_frequency ENUM('instant', 'daily', 'weekly') DEFAULT 'instant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**User Devices:**
```sql
CREATE TABLE user_devices (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    device_name VARCHAR(255) NOT NULL,
    device_type VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    last_active_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_current BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

**Workspaces:**
```sql
CREATE TABLE workspaces (
    id CHAR(36) PRIMARY KEY,
    owner_id CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    visibility ENUM('private', 'public') DEFAULT 'private',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id)
);
```

**Workspace Members:**
```sql
CREATE TABLE workspace_members (
    id CHAR(36) PRIMARY KEY,
    workspace_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    role ENUM('admin', 'member', 'guest') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_workspace_member (workspace_id, user_id),
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_workspace (workspace_id),
    INDEX idx_user (user_id)
);
```

**Boards:**
```sql
CREATE TABLE boards (
    id CHAR(36) PRIMARY KEY,
    workspace_id CHAR(36) NOT NULL,
    created_by CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    position INT DEFAULT 0,
    is_archived BOOLEAN DEFAULT FALSE,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_workspace (workspace_id),
    INDEX idx_archived (is_archived)
);
```

**Board Lists:**
```sql
CREATE TABLE board_lists (
    id CHAR(36) PRIMARY KEY,
    board_id CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    position INT DEFAULT 0,
    is_archived BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE,
    INDEX idx_board (board_id),
    INDEX idx_position (board_id, position)
);
```

**Cards:**
```sql
CREATE TABLE cards (
    id CHAR(36) PRIMARY KEY,
    list_id CHAR(36) NOT NULL,
    board_id CHAR(36) NOT NULL,
    title VARCHAR(500) NOT NULL,
    description TEXT NULL,
    position INT DEFAULT 0,
    due_date TIMESTAMP NULL,
    is_archived BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (list_id) REFERENCES board_lists(id) ON DELETE CASCADE,
    FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE,
    INDEX idx_list (list_id),
    INDEX idx_board (board_id),
    INDEX idx_due_date (due_date)
);
```

**Labels:**
```sql
CREATE TABLE labels (
    id CHAR(36) PRIMARY KEY,
    board_id CHAR(36) NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE,
    INDEX idx_board (board_id)
);

CREATE TABLE card_labels (
    card_id CHAR(36) NOT NULL,
    label_id CHAR(36) NOT NULL,
    PRIMARY KEY (card_id, label_id),
    FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE,
    FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
);
```

**Activity Logs:**
```sql
CREATE TABLE activity_logs (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    workspace_id CHAR(36) NULL,
    board_id CHAR(36) NULL,
    card_id CHAR(36) NULL,
    type VARCHAR(50) NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id, created_at),
    INDEX idx_workspace (workspace_id, created_at),
    INDEX idx_board (board_id, created_at),
    INDEX idx_card (card_id, created_at)
);
```

**Notification Preferences:**
```sql
CREATE TABLE notification_preferences (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL UNIQUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,
    in_app_notifications BOOLEAN DEFAULT TRUE,
    card_assigned BOOLEAN DEFAULT TRUE,
    card_mentioned BOOLEAN DEFAULT TRUE,
    card_due_soon BOOLEAN DEFAULT TRUE,
    card_moved BOOLEAN DEFAULT FALSE,
    comment_added BOOLEAN DEFAULT TRUE,
    digest_enabled BOOLEAN DEFAULT FALSE,
    digest_frequency ENUM('daily', 'weekly') DEFAULT 'daily',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Infrastructure Services Pattern

Infrastructure services (Transaction, Mailer, Storage, Cache, etc.) are **generic building blocks** that can be used by any use case. They are NOT tied to specific business operations.

### Key Principle

Infrastructure interfaces define **WHAT** capability is needed, implementations define **HOW** it's done. Use cases orchestrate generic tools with domain-specific repositories.

```
❌ Wrong - Tied to specific use case:
interface VerificationEmailInterface {
    public function sendVerificationEmail(string $email, string $token): void;
}

✅ Correct - Generic, reusable across application:
interface MailerInterface {
    public function send(string $to, string $subject, string $body, array $options = []): void;
    public function sendTemplate(string $to, string $template, array $data): void;
}
```

### File Structure

```
modules/Infrastructure/
├── Persistence/
│   ├── TransactionInterface.php        # Generic transaction wrapper
│   ├── EloquentTransaction.php         # Eloquent implementation
│   ├── Repositories/
│   └── Models/
├── Mail/
│   ├── MailerInterface.php             # Generic email sender
│   └── LaravelMailer.php               # Laravel Mail implementation
├── Storage/
│   ├── StorageInterface.php            # Generic file operations
│   └── LocalStorage.php                # Local filesystem implementation
└── Cache/
    ├── CacheInterface.php              # Generic key-value store
    └── RedisCache.php                  # Redis implementation
```

### Interface Definitions

**TransactionInterface** - Atomic database operations:
```php
interface TransactionInterface
{
    /**
     * Execute a callback within a database transaction.
     * Automatically commits on success, rolls back on failure.
     */
    public function execute(callable $callback): mixed;
}
```

**MailerInterface** - Email sending:
```php
interface MailerInterface
{
    /**
     * Send a plain email.
     */
    public function send(string $to, string $subject, string $body, array $options = []): void;

    /**
     * Send a templated email.
     */
    public function sendTemplate(string $to, string $template, array $data): void;
}
```

**StorageInterface** - File operations:
```php
interface StorageInterface
{
    public function put(string $path, mixed $contents): bool;
    public function get(string $path): ?string;
    public function exists(string $path): bool;
    public function delete(string $path): bool;
    public function url(string $path): string;
}
```

**CacheInterface** - Key-value store:
```php
interface CacheInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function put(string $key, mixed $value, ?int $ttl = null): bool;
    public function forget(string $key): bool;
    public function has(string $key): bool;
}
```

### Use Case Composition

Use cases compose generic infrastructure tools with domain-specific repositories:

```php
final readonly class SendVerificationEmailHandler
{
    public function __construct(
        private MailerInterface $mailer,                           // Generic infrastructure
        private SendVerificationEmailRepositoryInterface $repository, // Use-case-specific
    ) {}

    public function execute(SendVerificationEmailRequestDto $request): SendVerificationEmailResponseDto
    {
        $user = $this->repository->findUserById($request->userId);
        $token = $this->repository->createVerificationToken($user);

        $this->mailer->sendTemplate(
            to: $user->getEmail(),
            template: 'emails.verification',
            data: ['token' => $token->getValue()]
        );

        return new SendVerificationEmailResponseDto(
            sent: true,
            email: $user->getEmail(),
        );
    }
}
```

### Infrastructure Services Summary

| Interface | Purpose | Use Cases |
|-----------|---------|-----------|
| `TransactionInterface` | Atomic operations | Registration, email change, password reset, any multi-step write |
| `MailerInterface` | Send emails | Verification, password reset, notifications, invites, alerts |
| `StorageInterface` | File operations | Avatars, attachments, exports, imports |
| `CacheInterface` | Key-value store | Sessions, rate limiting, temporary tokens |

### Binding in Service Providers

```php
// modules/Infrastructure/Providers/InfrastructureServiceProvider.php
public function register(): void
{
    // Generic infrastructure services
    $this->app->bind(TransactionInterface::class, EloquentTransaction::class);
    $this->app->bind(MailerInterface::class, LaravelMailer::class);
    $this->app->bind(StorageInterface::class, LocalStorage::class);
    $this->app->bind(CacheInterface::class, RedisCache::class);
}
```

### Testing Benefits

Generic interfaces make testing easier - mock once, reuse everywhere:

```php
// Single mock for all mailer tests
$mockMailer = $this->mock(MailerInterface::class);
$mockMailer->shouldReceive('sendTemplate')->once();

// Use in any handler test
$handler = new SendVerificationEmailHandler($mockMailer, $mockTokens, $mockUsers);
```

---

## Decisions Made

| Decision | Choice | Notes |
|----------|--------|-------|
| Architecture | Layered by domain | Keep current structure |
| Profile operations | Under `Member/` | All member-related operations in one place |
| MFA | Defer to later phase | Structure in place, implementation later |
| Billing | Wait entirely | No placeholder until payment implementation |
| Terminology | Workspace | Workspaces contain members and boards |
| Activity Log | Board/workspace only | Account activity deferred |
| User vs Member | Single User entity | Member is a read-model of User |

---

## Routes Structure

```
routes/
├── api.php                      # Auth routes (existing)
│   └── POST /auth/register
│   └── POST /auth/login
│   └── POST /auth/logout
│
└── api_v1.php                   # Versioned API routes
    │
    │  # Member Profile
    ├── GET    /v1/members/me                # Existing
    ├── PATCH  /v1/members/me/profile         # New - UpdateProfile
    ├── POST   /v1/members/me/avatar          # New - UpdateAvatar
    │
    │  # Member Email
    ├── POST   /v1/members/me/email           # New - ChangeEmail
    ├── POST   /v1/members/me/email/verify    # New - VerifyEmail
    │
    │  # Member Security
    ├── PATCH  /v1/members/me/password        # New - ChangePassword
    ├── GET    /v1/members/me/devices         # New - GetDevices
    ├── DELETE /v1/members/me/devices/:id      # New - RevokeDevice
    │
    │  # Member Preferences
    ├── GET    /v1/members/me/preferences     # New - GetPreferences
    ├── PATCH  /v1/members/me/preferences     # New - UpdatePreferences
    ├── GET    /v1/members/me/privacy          # New - GetPrivacySettings
    ├── PATCH  /v1/members/me/privacy          # New - UpdatePrivacySettings
    │
    │  # Member Account
    ├── DELETE /v1/members/me                  # New - DeleteAccount
    │
    │  # Notifications
    ├── GET    /v1/notifications/preferences   # New
    ├── PATCH  /v1/notifications/preferences   # New
    │
    │  # Workspaces
    ├── POST   /v1/workspaces                  # New - CreateWorkspace
    ├── GET    /v1/workspaces/:id              # New - GetWorkspace
    ├── POST   /v1/workspaces/:id/members      # New - InviteMember
    ├── DELETE /v1/workspaces/:id/members/:uid # New - RemoveMember
    ├── PATCH  /v1/workspaces/:id/members/:uid # New - UpdateMemberRole
    │
    │  # Boards
    ├── POST   /v1/boards                      # New - CreateBoard
    ├── GET    /v1/boards/:id                  # New - GetBoard
    ├── PATCH  /v1/boards/:id                  # New - UpdateBoard
    ├── POST   /v1/boards/:id/lists            # New - CreateList
    ├── PATCH  /v1/lists/:id                   # New - ReorderList
    ├── POST   /v1/lists/:id/cards             # New - CreateCard
    ├── PATCH  /v1/cards/:id                   # New - UpdateCard
    ├── POST   /v1/cards/:id/move              # New - MoveCard
    │
    │  # Activity
    └── GET    /v1/activity                    # New - GetActivityFeed
```

---

## Implementation Phases

### Phase 1: Member Profile & Security
1. `UpdateProfile` - Update names, username, bio
2. `UpdateAvatar` - Update profile picture
3. `ChangeEmail` / `VerifyEmail` - Email management
4. `ChangePassword` - Password change
5. `GetDevices` / `RevokeDevice` - Device management
6. `GetPreferences` / `UpdatePreferences` - User preferences
7. `GetPrivacySettings` / `UpdatePrivacySettings` - Privacy settings
8. `DeleteAccount` - Account deletion

### Phase 2: Workspace Features
1. `CreateWorkspace` - Create workspace
2. `GetWorkspace` - Get workspace details
3. `InviteMember` - Invite users
4. `RemoveMember` - Remove members
5. `UpdateMemberRole` - Change roles

### Phase 3: Board Features
1. `CreateBoard` - Create board
2. `GetBoard` - Get board with lists/cards
3. `UpdateBoard` / `ArchiveBoard` - Board management
4. `CreateList` / `ReorderList` - List management
5. `CreateCard` / `MoveCard` / `UpdateCard` - Card management

### Phase 4: Activity & Notifications
1. Activity logging infrastructure
2. `GetActivityFeed` / `GetCardActivity` / `GetBoardActivity`
3. Notification preferences CRUD

### Phase 5: MFA & Billing (Future)
1. MFA implementation
2. Payment methods
3. Subscription management

---

## Critical Files (Existing Patterns to Follow)

| Pattern | File |
|---------|------|
| Domain Entity | `modules/Domain/User/User.php` |
| Use Case Handler | `modules/Application/UseCases/Member/GetMember/GetMemberHandler.php` |
| DTO | `modules/Application/UseCases/Member/GetMember/GetMemberResponseDto.php` |
| Repository Interface | `modules/Application/UseCases/Member/GetMember/GetMemberRepositoryInterface.php` |
| Eloquent Model | `modules/Infrastructure/Persistence/Models/UserModel.php` |
| Mapper | `modules/Infrastructure/Persistence/Mappers/UserMapper.php` |
| Repository | `modules/Infrastructure/Persistence/Repositories/GetMemberRepository.php` |
| Controller | `modules/Infrastructure/Http/Controllers/Member/GetMemberController.php` |
| Service Provider | `modules/Infrastructure/Providers/RepositoryServiceProvider.php` |

---

## Verification

After each implementation:
1. Run `php artisan test --compact` to verify tests pass
2. Run `vendor/bin/pint --dirty --format agent` for code style
3. Use `php artisan route:list` to verify endpoints
4. Write feature tests for new use cases