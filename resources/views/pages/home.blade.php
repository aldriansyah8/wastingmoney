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
                  <span id="releasedYearTriggerText">{{ date('Y') }}</span>
                  <i data-lucide="chevron-down" class="wm-dropdown-chevron"></i>
                </button>
                <div class="wm-dropdown-menu home-dropdown-menu" id="releasedYearMenu">
                  @php
                    $currentYear = (int) date('Y');
                    $minYear = 1950;
                  @endphp
                  @for ($yr = $currentYear; $yr >= $minYear; $yr--)
                  <div 
                    class="wm-dropdown-item home-dropdown-item {{ $yr === $currentYear ? 'selected' : '' }}" 
                    data-value="{{ $yr }}" 
                    onclick="selectGenericOption('releasedYear', 'releasedYearTriggerText', '{{ $yr }}', '{{ $yr }}', 'releasedYearMenu', 'releasedYearBtn')"
                  >
                    <span>{{ $yr }}</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  @endfor
                </div>
                <input type="hidden" id="releasedYear" name="releasedYear" value="{{ date('Y') }}" />
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
              placeholder="Taylor Swift North America Tour"
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
            <!-- Upload Button & Thumbnail Box -->
            <div class="flex flex-col items-center">
              <label class="home-upload-box relative overflow-hidden group cursor-pointer" id="homeUploadBox" title="Klik untuk mengunggah foto merchandise">
                <!-- Icon Upload (Saat belum ada foto dipilih) -->
                <i data-lucide="upload" id="uploadDefaultIcon" class="w-5 h-5 text-white transition-transform group-hover:scale-110"></i>
                
                <!-- Thumbnail Preview Langsung di Box (Saat foto sudah dipilih) -->
                <img id="uploadBoxThumbImg" src="" alt="Thumbnail" class="hidden absolute inset-0 w-full h-full object-cover rounded-xl" />

                <input
                  type="file"
                  id="merchImageInput"
                  name="image"
                  accept="image/*"
                  onchange="handleFileSelected(this)"
                  class="hidden"
                />
              </label>
              <span id="fileNameLabel" class="home-file-badge max-w-[120px] truncate block text-center" title="Nama file foto yang diunggah">
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

          <!-- Bar Kartu Preview Foto Hasil Upload (Tampil saat user memilih gambar di form) -->
          <div id="formImagePreviewBar" class="hidden mt-3 p-3 rounded-2xl bg-indigo-50/70 border border-indigo-100 flex items-center justify-between gap-3 animate-in fade-in duration-200">
            <div class="flex items-center gap-3 min-w-0">
              <!-- Thumbnail preview clickable -->
              <div 
                class="w-12 h-12 rounded-xl bg-white border border-indigo-200/80 overflow-hidden flex-shrink-0 cursor-pointer shadow-xs hover:border-[#4e46e5] transition-all relative group"
                onclick="openHomeImagePreviewModal()"
                title="Klik untuk memperbesar dan melihat preview foto"
              >
                <img id="formImagePreviewThumb" src="" alt="Preview Foto" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity">
                  <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                </div>
              </div>

              <!-- Info & Tombol Lihat Preview -->
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                  <p id="formImagePreviewName" class="text-xs font-bold text-slate-800 truncate max-w-[160px] sm:max-w-[220px]">foto_merch.jpg</p>
                </div>
                <div class="flex items-center gap-3 mt-1">
                  <button 
                    type="button" 
                    onclick="openHomeImagePreviewModal()" 
                    class="inline-flex items-center gap-1 text-[11px] font-bold text-[#4e46e5] hover:text-[#4338ca] hover:underline cursor-pointer"
                    title="Lihat hasil foto yang diunggah secara penuh"
                  >
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>Preview Foto</span>
                  </button>
                  <label for="merchImageInput" class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-slate-800 cursor-pointer" title="Ganti dengan foto lain">
                    <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                    <span>Ganti Foto</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Tombol Hapus Foto Terpilih -->
            <button 
              type="button" 
              onclick="clearSelectedImage()" 
              class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" 
              title="Batalkan / Hapus foto yang dipilih"
            >
              <i data-lucide="trash-2" class="w-4 h-4"></i>
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

  <!-- ================= MODAL PREVIEW FOTO HASIL UPLOAD USER (SEBELUM SUBMIT) ================= -->
  <div 
    id="homeImagePreviewModal" 
    class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 transition-all"
    onclick="handleHomePreviewBackdrop(event)"
    role="dialog"
    aria-modal="true"
  >
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150" onclick="event.stopPropagation()">
      <!-- Modal Header -->
      <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center text-[#4e46e5]">
            <i data-lucide="image" class="w-4 h-4"></i>
          </div>
          <div>
            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Preview Foto Merchandise</h3>
            <p id="homeImagePreviewSubtitle" class="text-[11px] text-slate-500 font-mono truncate max-w-[280px]">img-01.jpg</p>
          </div>
        </div>
        <button 
          type="button" 
          onclick="closeHomeImagePreviewModal()" 
          class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors cursor-pointer"
          title="Tutup Preview (Esc)"
        >
          <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
      </div>

      <!-- Modal Body: Image Preview Container -->
      <div class="p-4 bg-slate-100/70 flex items-center justify-center min-h-[260px] max-h-[65vh] overflow-hidden">
        <img 
          id="homeImagePreviewFull" 
          src="" 
          alt="Preview Foto Merchandise" 
          class="max-w-full max-h-[60vh] object-contain rounded-xl shadow-sm bg-white" 
        />
      </div>

      <!-- Modal Footer -->
      <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-white text-xs">
        <span class="text-slate-400 text-[11px]">Pastikan foto terlihat jelas sebelum disubmit</span>
        <button 
          type="button" 
          onclick="closeHomeImagePreviewModal()" 
          class="px-4 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-all cursor-pointer text-xs"
        >
          Tutup Preview
        </button>
      </div>
    </div>
  </div>

  <!-- Reusable Footer Partial -->
  @include('partials.footer')
</div>
@endsection

@section('scripts')
<script src="/js/home.js"></script>
@endsection

