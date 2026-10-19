@extends('layouts.app')

@section('title', 'wastingmoney - Home')

@section('styles')
  <!-- Dedicated CSS for Home Page User (Separated from Blade) -->
  <link rel="stylesheet" href="/css/home.css">
@endsection

@section('content')
<div class="home-page-container">
  
  <!-- Reusable Header Partial -->
  @include('partials.header')

  <!-- ================= MAIN CONTENT ================= -->
  <main class="home-main-container">
    
    <!-- HERO CONCERT BANNER -->
    <div class="home-banner-wrapper">
      <img 
        src="/assets/hero_banner.png" 
        alt="Hunt down your goods and wasting your money" 
        class="home-banner-img"
      />
    </div>

    <!-- TWO-COLUMN SECTION: FORM (LEFT) & ARTWORK (RIGHT) -->
    <div class="home-grid-section">
      
      <!-- LEFT SIDE: REQUEST FORM -->
      <div class="home-grid-form-col">
        <div class="mb-6">
          <p class="home-subtitle">
            We heard you and we searching for you,
          </p>
          <h2 class="home-title">
            just request!
          </h2>
        </div>

        <!-- Alert Feedback Messages -->
        <div id="formSuccessAlert" class="hidden mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-2.5">
          <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
          <span>Permintaan berhasil diajukan! Admin akan segera memprosesnya.</span>
        </div>

        <div id="formErrorAlert" class="hidden mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm flex items-start gap-2.5">
          <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
          <span id="formErrorMessage">Field Artist/band wajib diisi.</span>
        </div>

        <form id="merchForm" class="space-y-4">
          @csrf

          <!-- Artist/band -->
          <div>
            <label class="home-form-label">
              Artist/band
            </label>
            <input
              type="text"
              id="artistBand"
              name="artistBand"
              placeholder="Taylor Swift"
              required
              class="home-text-input"
            />
          </div>

          <!-- Merch Type & Released Year in one row -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="home-form-label">
                Merch Type
              </label>
              <div class="wm-dropdown-wrapper" id="merchTypeWrapper">
                <button 
                  type="button" 
                  class="wm-dropdown-trigger home-dropdown-trigger" 
                  id="merchTypeBtn" 
                  onclick="toggleDropdownMenu('merchTypeMenu', 'merchTypeBtn')"
                >
                  <span id="merchTypeTriggerText">T-Shirt</span>
                  <i data-lucide="chevron-down" class="wm-dropdown-chevron"></i>
                </button>
                <div class="wm-dropdown-menu home-dropdown-menu" id="merchTypeMenu">
                  <div class="wm-dropdown-item home-dropdown-item selected" data-value="T-Shirt" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'T-Shirt', 'T-Shirt', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>T-Shirt</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Vinyl" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Vinyl', 'Vinyl', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Vinyl</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="CD" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'CD', 'CD', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>CD</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Hoodie" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Hoodie', 'Hoodie', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Hoodie</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Jacket" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Jacket', 'Jacket', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Jacket</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Poster" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Poster', 'Poster', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Poster</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Accessories" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Accessories', 'Accessories', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Accessories</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Other" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Other', 'Other', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Other</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                </div>
                <input type="hidden" id="merchType" name="merchType" value="T-Shirt" />
              </div>
            </div>

            <div>
              <label class="home-form-label">
                Released Year
              </label>
              <div class="wm-dropdown-wrapper" id="releasedYearWrapper">
                <button 
                  type="button" 
                  class="wm-dropdown-trigger home-dropdown-trigger" 
                  id="releasedYearBtn" 
                  onclick="toggleDropdownMenu('releasedYearMenu', 'releasedYearBtn')"
                >
                  <span id="releasedYearTriggerText">2026</span>
                  <i data-lucide="chevron-down" class="wm-dropdown-chevron"></i>
                </button>
                <div class="wm-dropdown-menu home-dropdown-menu" id="releasedYearMenu">
                  @foreach(['2026', '2025', '2024', '2023', '2022', '2021', '2020', '2019', '2018', '2017', '2016', '2015', '2010', '2009', '2000', '1990s'] as $yr)
                  <div 
                    class="wm-dropdown-item home-dropdown-item {{ $yr === '2026' ? 'selected' : '' }}" 
                    data-value="{{ $yr }}" 
                    onclick="selectGenericOption('releasedYear', 'releasedYearTriggerText', '{{ $yr }}', '{{ $yr }}', 'releasedYearMenu', 'releasedYearBtn')"
                  >
                    <span>{{ $yr }}</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  @endforeach
                </div>
                <input type="hidden" id="releasedYear" name="releasedYear" value="2026" />
              </div>
            </div>
          </div>

          <!-- Merch Edition -->
          <div>
            <label class="home-form-label">
              Merch Edition
            </label>
            <input
              type="text"
              id="merchEdition"
              name="merchEdition"
              placeholder="Taylor Swift Nort America Tour"
              class="home-text-input"
            />
          </div>

          <!-- More Description -->
          <div>
            <label class="home-form-label">
              More Description
            </label>
            <textarea
              rows="4"
              id="moreDescription"
              name="description"
              placeholder="Taylor Swift North American Tour 2016, Long Sleeve, White with tour list"
              class="home-textarea-input"
            ></textarea>
          </div>

          <!-- Buttons Row: Upload Box + Submit Button -->
          <div class="flex items-start gap-3 pt-2">
            <!-- Upload Button -->
            <div class="flex flex-col items-center">
              <label class="home-upload-box">
                <i data-lucide="upload" class="w-5 h-5"></i>
                <input
                  type="file"
                  accept="image/*"
                  onchange="handleFileSelected(this)"
                  class="hidden"
                />
              </label>
              <span id="fileNameLabel" class="home-file-badge">
                img-01.jpg
              </span>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              id="submitButton"
              class="home-submit-btn"
            >
              <span id="submitLabel">Submit</span>
              <span id="submitLoading" class="hidden">
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
              </span>
            </button>
          </div>
        </form>
      </div>

      <!-- RIGHT SIDE: POSTER ARTWORK -->
      <div class="home-grid-artwork-col home-artwork-wrapper">
        <img 
          src="/assets/poster_art.png" 
          alt="Life at your own rhythm" 
          class="home-artwork-img"
        />
      </div>

    </div>
  </main>

  <!-- Reusable Footer Partial -->
  @include('partials.footer')
</div>
@endsection

@section('scripts')
<script src="/js/home.js"></script>
@endsection

