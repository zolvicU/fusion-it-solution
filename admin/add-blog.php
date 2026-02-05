<?php require_once 'includes/auth.php'; ?>
<?php

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

if (!isset($pdo)) {
    die("Database connection failed.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title   = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    $errors = [];

    if (empty($title))   $errors[] = "Title is required.";
    if (empty($content)) $errors[] = "Content is required.";
    if (empty($slug))    $errors[] = "Slug could not be generated from title.";

    $image_name = "";
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../assets/uploads/blog/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $errors[] = "File is not a valid image.";
        } elseif ($_FILES["image"]["size"] > 5000000) {
            $errors[] = "Image too large (max 5MB).";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Only JPG, JPEG, PNG & GIF allowed.";
        } elseif (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $errors[] = "Failed to upload image.";
        }
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO blog_posts (title, slug, content, image) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $slug, $content, $image_name]);

            $message = "<div class='alert success'>Blog post added successfully!</div>";
            $title = $content = "";
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "A post with this title/slug already exists. Try a different title.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
            $message = "<div class='alert error'>" . implode("<br>", $errors) . "</div>";
        }
    } else {
        $message = "<div class='alert error'>" . implode("<br>", $errors) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blog Post | Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/add-blog.css">
</head>

<body>
    <div class="container">
        <div class="page-header">
            <h1>Add New Blog Post</h1>
            <p>Create engaging content with proper formatting and SEO optimization</p>
        </div>

        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <div class="main-card">
            <div class="card-header">
                <h2>Post Details</h2>
                <p>Fill in all required fields to publish your post</p>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" class="form-grid">
                    <!-- Title Field -->
                    <div class="form-group">
                        <label for="title">Post Title</label>
                        <div class="label-hint">Keep it compelling and SEO-friendly (60-70 characters)</div>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" 
                               required 
                               placeholder="Enter a compelling blog post title"
                               maxlength="120"
                               oninput="updateSlugPreview()">
                        <div class="char-counter" id="titleCounter">0/120 characters</div>
                        <div class="slug-preview" id="slugPreview">
                            <span>URL will be:</span> <span id="slugText">your-title-here</span>
                        </div>
                    </div>

                    <!-- Content Field -->
                    <div class="form-group">
                        <label for="content">Content</label>
                        <div class="label-hint">Write your complete blog post content</div>
                        <textarea name="content" 
                                  id="content" 
                                  required 
                                  placeholder="Start writing your blog post here..."
                                  oninput="updateContentCounter()"><?= isset($content) ? htmlspecialchars($content) : '' ?></textarea>
                        <div class="char-counter" id="contentCounter">
                            <span id="wordCount">0</span> words • <span id="charCount">0</span> characters
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="form-group">
                        <label>Featured Image</label>
                        <div class="label-hint">Optional - Adds visual appeal to your post</div>
                        
                        <div class="file-upload-area" id="uploadArea">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">
                                <h4>Drag & drop or click to upload</h4>
                                <p>Recommended: 1200×630px • Max 5MB</p>
                            </div>
                            <div class="file-info">
                                <i class="fas fa-file-image"></i>
                                <span>JPG, PNG, or GIF</span>
                            </div>
                            <input type="file" 
                                   name="image" 
                                   id="image" 
                                   accept="image/*" 
                                   class="file-input"
                                   onchange="handleFileSelect(event)">
                        </div>
                        
                        <div class="image-preview" id="imagePreview">
                            <div style="font-size: 13px; color: var(--gray-700); margin-bottom: 8px;">
                                <i class="fas fa-image"></i> Selected Image:
                            </div>
                            <img id="previewImage" class="preview-image" alt="Preview">
                            <div style="margin-top: 8px;">
                                <button type="button" class="btn btn-outline" onclick="removeImage()" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-trash"></i> Remove Image
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        Publish Post
                    </button>
                </form>

                <!-- Action Bar -->
                <div class="action-bar">
                    <a href="blog-list.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Back to Blog List
                    </a>
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Title character counter
        const titleInput = document.getElementById('title');
        const titleCounter = document.getElementById('titleCounter');
        
        titleInput.addEventListener('input', function() {
            const length = this.value.length;
            titleCounter.textContent = `${length}/120 characters`;
            
            if (length > 100) {
                titleCounter.classList.add('warning');
                titleCounter.classList.remove('error');
            } else if (length > 110) {
                titleCounter.classList.add('error');
                titleCounter.classList.remove('warning');
            } else {
                titleCounter.classList.remove('warning', 'error');
            }
        });
        
        // Trigger initial count
        titleInput.dispatchEvent(new Event('input'));
        
        // Content counter
        const contentInput = document.getElementById('content');
        const wordCountSpan = document.getElementById('wordCount');
        const charCountSpan = document.getElementById('charCount');
        
        function updateContentCounter() {
            const text = contentInput.value;
            const charCount = text.length;
            const wordCount = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
            
            wordCountSpan.textContent = wordCount;
            charCountSpan.textContent = charCount;
            
            const counter = document.getElementById('contentCounter');
            if (charCount > 5000) {
                counter.classList.add('warning');
                counter.classList.remove('error');
            } else if (charCount > 10000) {
                counter.classList.add('error');
                counter.classList.remove('warning');
            } else {
                counter.classList.remove('warning', 'error');
            }
        }
        
        contentInput.addEventListener('input', updateContentCounter);
        updateContentCounter();
        
        // Slug preview
        function updateSlugPreview() {
            const title = titleInput.value;
            const slug = title
                .toLowerCase()
                .replace(/[^\w\s]/gi, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();
            
            document.getElementById('slugText').textContent = slug || 'your-title-here';
        }
        
        // File upload handling
        function handleFileSelect(event) {
            const file = event.target.files[0];
            const uploadArea = document.getElementById('uploadArea');
            const imagePreview = document.getElementById('imagePreview');
            const previewImage = document.getElementById('previewImage');
            
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF).');
                    event.target.value = '';
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB.');
                    event.target.value = '';
                    return;
                }
                
                // Update UI
                uploadArea.classList.add('has-file');
                uploadArea.querySelector('.upload-text h4').textContent = file.name;
                uploadArea.querySelector('.upload-text p').textContent = 
                    `Size: ${(file.size / 1024 / 1024).toFixed(2)}MB`;
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    imagePreview.classList.add('show');
                };
                reader.readAsDataURL(file);
            }
        }
        
        function removeImage() {
            const fileInput = document.getElementById('image');
            const uploadArea = document.getElementById('uploadArea');
            const imagePreview = document.getElementById('imagePreview');
            
            fileInput.value = '';
            uploadArea.classList.remove('has-file');
            uploadArea.querySelector('.upload-text h4').textContent = 'Drag & drop or click to upload';
            uploadArea.querySelector('.upload-text p').textContent = 'Recommended: 1200×630px • Max 5MB';
            imagePreview.classList.remove('show');
        }
        
        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--primary)';
            this.style.backgroundColor = 'var(--primary-50)';
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '';
            this.style.backgroundColor = '';
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '';
            this.style.backgroundColor = '';
            
            const fileInput = document.getElementById('image');
            const files = e.dataTransfer.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect({ target: fileInput });
            }
        });
        
        // Form submission loading state
        const form = document.querySelector('form');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function() {
            submitBtn.innerHTML = '<span class="loading"></span> Publishing...';
            submitBtn.disabled = true;
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S to save draft
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                submitBtn.click();
            }
            
            // Ctrl/Cmd + Enter to submit
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                submitBtn.click();
            }
        });
        
        // Auto-save functionality (simulated)
        let autoSaveTimer;
        contentInput.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                console.log('Auto-save triggered...');
                // In a real application, you would send an AJAX request here
            }, 3000);
        });
        
        // Initialize slug preview
        updateSlugPreview();
    </script>
</body>
</html>