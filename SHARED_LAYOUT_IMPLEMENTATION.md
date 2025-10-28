# Shared Layout System - Public Pages

## 📋 Overview

Created a centralized layout system for all public-facing pages to ensure consistent head meta tags, Open Graph data, and Twitter Card implementation across the Aurora application.

---

## 🗂️ File Structure

```
resources/
  views/
    layouts/
      public.blade.php          ← Master layout for public pages
    home.blade.php              ← Updated to extend public layout
    plantscan.blade.php         ← Updated to extend public layout
```

---

## 📄 Layout File: `layouts/public.blade.php`

### **Features:**

1. **Centralized HTML Structure**
   - DOCTYPE and HTML lang declarations
   - Consistent charset and viewport meta tags
   - Favicon management

2. **SEO & Social Media Meta Tags**
   - Open Graph (Facebook) tags
   - Twitter Card tags
   - All customizable via `@section` directives

3. **Stack-Based Content Injection**
   - `@stack('styles')` - For page-specific CSS
   - `@stack('head')` - For additional head content
   - `@stack('scripts')` - For page-specific JavaScript

4. **Flexible Sections**
   - `@yield('title')` - Page title
   - `@yield('og_title')` - Open Graph title
   - `@yield('og_description')` - Open Graph description
   - `@yield('og_image')` - Open Graph image
   - `@yield('og_url')` - Open Graph URL
   - `@yield('og_type')` - Open Graph type
   - `@yield('twitter_*')` - Twitter Card equivalents
   - `@yield('content')` - Main page content

---

## 🎨 Default Values

If a page doesn't override these sections, the layout uses these defaults:

| Meta Tag | Default Value |
|----------|---------------|
| **Title** | Aurora Pets |
| **OG Title** | Aurora |
| **OG Description** | Servicios funerarios para mascotas en Ecuador |
| **OG Image** | assets/home/imgs/11.png |
| **OG URL** | https://auroraurn.pet |
| **OG Type** | website |
| **Twitter Title** | Aurora |
| **Twitter Description** | Servicios funerarios para mascotas en Ecuador |
| **Twitter Image** | assets/home/imgs/11.png |

---

## 🔧 How to Use the Layout

### **Basic Example:**

```blade
@extends('layouts.public')

@section('title', 'My Page Title')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/my-styles.css') }}">
@endpush

@section('content')
    <h1>My Page Content</h1>
@endsection

@push('scripts')
    <script src="{{ asset('js/my-script.js') }}"></script>
@endpush
```

### **With Custom OG Tags:**

```blade
@extends('layouts.public')

@section('title', 'PlantScan')

@section('og_title', 'Aurora PlantScan')
@section('og_description', 'Find your pet's perfect plant')
@section('og_image', 'https://auroraurn.pet/assets/plantscan.png')
@section('og_url', 'https://auroraurn.pet/plantscan')

@section('content')
    <!-- Your content here -->
@endsection
```

---

## 📱 Pages Currently Using the Layout

### **1. Home Page (`home.blade.php`)**

**Before:**
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- ... lots of meta tags ... -->
</head>
<body>
    <!-- content -->
</body>
</html>
```

**After:**
```blade
@extends('layouts.public')

@section('title', 'Aurora Pets')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home-style.css') }}">
@endpush

@section('content')
    <!-- content -->
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // home page scripts
    </script>
@endpush
```

**Benefits:**
- ✅ Removed 20 lines of duplicate head tags
- ✅ Consistent meta tags with site defaults
- ✅ Cleaner, more maintainable code

---

### **2. PlantScan Page (`plantscan.blade.php`)**

**Before:**
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- ... SEO meta tags ... -->
    <!-- ... Open Graph tags ... -->
    <!-- ... Twitter Card tags ... -->
    <!-- ... Google Fonts ... -->
    <!-- ... GSAP scripts ... -->
    <!-- ... Phosphor Icons ... -->
    <!-- ... Custom CSS ... -->
</head>
<body>
    <!-- content -->
</body>
</html>
```

**After:**
```blade
@extends('layouts.public')

@section('title', 'Aurora Plant Scan')

@section('og_title', 'Aurora Pets - Plant Scan')
@section('og_description', 'Cada mascota tiene una planta que la representa. ¿Cuál es la tuya?')
@section('og_image', 'https://auroraurn.pet/assets/plantscan/imgs/11.png')
@section('og_url', 'https://auroraurn.pet/')

@push('head')
    <!-- Page-specific meta tags -->
    <meta name="description" content="...">
    <!-- Google Fonts -->
    <link href="..." rel="stylesheet">
    <!-- GSAP Scripts -->
    <script src="..."></script>
    <!-- Phosphor Icons -->
    <script src="..."></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="./css/aurora-general.css">
    <link rel="stylesheet" href="./css/prevention-style.css">
@endpush

@section('content')
    <!-- content -->
@endsection

@push('scripts')
    <script src="./js/prevention.js"></script>
    <script>
        // plantscan scripts
    </script>
@endpush
```

**Benefits:**
- ✅ Consistent OG/Twitter tags with home page
- ✅ Page-specific meta tags preserved
- ✅ External scripts organized in stacks
- ✅ Removed ~30 lines of duplicate HTML structure

---

## 🎯 Benefits of This Approach

### **1. Consistency**
- All public pages share the same base HTML structure
- Open Graph and Twitter Card tags are consistent
- Favicon is centralized

### **2. Maintainability**
- Update meta tags in one place (the layout)
- Changes propagate to all pages automatically
- Easier to add new meta tags in the future

### **3. Flexibility**
- Pages can override default values as needed
- Pages can add page-specific head content
- Stack system allows adding scripts/styles anywhere in the page

### **4. Performance**
- Encourages proper script placement (end of body)
- Organized asset loading
- Easier to implement lazy loading strategies

### **5. SEO**
- Consistent Open Graph implementation
- Proper Twitter Card tags
- Better social media sharing previews

---

## 🔄 Migration Guide for New Pages

When creating a new public page:

1. **Start with the extends directive:**
   ```blade
   @extends('layouts.public')
   ```

2. **Set the page title:**
   ```blade
   @section('title', 'Your Page Title')
   ```

3. **Customize OG tags if needed:**
   ```blade
   @section('og_title', 'Custom OG Title')
   @section('og_description', 'Custom description...')
   @section('og_image', 'https://...')
   ```

4. **Add page-specific styles:**
   ```blade
   @push('styles')
       <link rel="stylesheet" href="...">
   @endpush
   ```

5. **Add your content:**
   ```blade
   @section('content')
       <!-- Your HTML here -->
   @endsection
   ```

6. **Add page-specific scripts:**
   ```blade
   @push('scripts')
       <script src="..."></script>
       <script>
           // Your JS here
       </script>
   @endpush
   ```

---

## 📊 Statistics

### **Code Reduction:**

| File | Before | After | Saved |
|------|--------|-------|-------|
| home.blade.php | ~250 lines | ~230 lines | 20 lines |
| plantscan.blade.php | ~820 lines | ~790 lines | 30 lines |
| **Total** | | | **50 lines** |

### **Duplicate Code Eliminated:**

- ✅ 2 sets of DOCTYPE/HTML declarations
- ✅ 2 sets of charset/viewport meta tags
- ✅ 2 sets of Open Graph tags (8 meta tags each)
- ✅ 2 sets of Twitter Card tags (4 meta tags each)
- ✅ 2 sets of closing body/html tags

---

## 🚀 Future Enhancements

### **Potential Improvements:**

1. **Component-based Approach:**
   - Create Blade components for common sections
   - E.g., `<x-meta-tags :title="..." :description="..." />`

2. **Dynamic OG Images:**
   - Generate OG images based on page content
   - Use the Canvas system from PlantScan results

3. **Structured Data:**
   - Add JSON-LD schema markup
   - Improve SEO with rich snippets

4. **Multiple Layouts:**
   - Create `layouts/admin.blade.php` for admin pages
   - Create `layouts/profile.blade.php` for profile pages

5. **Asset Versioning:**
   - Use Laravel Mix/Vite for asset versioning
   - Implement cache-busting strategies

---

## 🧪 Testing Checklist

- [ ] Home page loads correctly
- [ ] PlantScan page loads correctly
- [ ] All stylesheets load properly
- [ ] All scripts execute in correct order
- [ ] Open Graph tags display correctly in Facebook debugger
- [ ] Twitter Card tags display correctly in Twitter validator
- [ ] Page titles appear correctly in browser tabs
- [ ] Favicon displays in all browsers
- [ ] Mobile viewport is correct
- [ ] No console errors on any page

---

## 🔗 Related Files

- `resources/views/layouts/public.blade.php` - Master layout
- `resources/views/home.blade.php` - Home page implementation
- `resources/views/plantscan.blade.php` - PlantScan implementation
- `SOCIAL_SHARING_IMPLEMENTATION.md` - Related social media documentation

---

**Implementation Date:** October 6, 2025  
**Developer Notes:** This layout system follows Laravel best practices and provides a solid foundation for future public pages. The stack-based approach offers maximum flexibility while maintaining consistency.
