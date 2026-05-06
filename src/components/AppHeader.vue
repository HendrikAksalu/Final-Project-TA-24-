<script setup>
import brandLogoSrc from '@/assets/logo.png'

defineProps({
  backTo: {
    type: [String, Object],
    default: null,
  },
  showMenu: {
    type: Boolean,
    default: true,
  },
  showAuthLinks: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['menu-click'])
</script>

<template>
  <header class="topbar">
    <RouterLink v-if="backTo" :to="backTo" class="icon-link" aria-label="Mine tagasi">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path
          d="M14.9 5.2 8.1 12l6.8 6.8-1.8 1.8L4.5 12l8.6-8.6 1.8 1.8Z"
          fill="currentColor"
        />
      </svg>
    </RouterLink>

    <RouterLink to="/" class="brand-link" aria-label="Fototeek avaleht">
      <img class="brand-logo" :src="brandLogoSrc" alt="" width="112" height="114" />
      <span class="brand-text" aria-hidden="true">Fototeek</span>
    </RouterLink>

    <nav v-if="showAuthLinks" class="auth-links">
      <RouterLink to="/logi-sisse">Logi sisse</RouterLink>
      <RouterLink class="register" to="/registreeru">Registreeru</RouterLink>
    </nav>
    <button
      v-else-if="showMenu"
      class="icon-link"
      type="button"
      aria-label="Veel tegevusi"
      @click="$emit('menu-click')"
    >
      ⋮
    </button>
    <div v-else class="left-spacer" aria-hidden="true"></div>
  </header>
</template>

<style scoped>
.topbar {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 64px;
  
}

.topbar::after {
  content: '';
  position: absolute;
  left: calc(50% - 50vw);
  bottom: 0;
  width: 100vw;
  border-bottom: 1px solid var(--line-soft, #e4ddd1);
  pointer-events: none;
}

.icon-link {
  border: none;
  background: transparent;
  text-decoration: none;
  color: var(--ink, #231f20);
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  font-size: 24px;
  line-height: 1;
  position: relative;
  top: -12px;
}

.icon-link svg {
  width: 28px;
  height: 28px;
}

.left-spacer {
  width: 32px;
  height: 32px;
  position: relative;
  top: -12px;
}

.brand-link {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: inherit;
  position: relative;
  top: -12px;
}

.brand-link:visited,
.brand-link:hover {
  color: inherit;
}

.brand-link:focus {
  outline: none;
}

.brand-link:focus-visible {
  outline: 2px solid var(--ink, #231f20);
  outline-offset: 3px;
}

.brand-logo {
  width: 26px;
  height: 26px;
  object-fit: contain;
  flex-shrink: 0;
}

.brand-text {
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
  font-size: 18px;
  line-height: 1;
}

.auth-links {
  display: flex;
  align-items: center;
  gap: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 11px;
  min-height: 34px;
  position: relative;
  top: -12px;
}

.auth-links a {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 34px;
  color: var(--ink, #231f20);
  text-decoration: none;
  line-height: 1;
}

.auth-links .register {
  background: #231f20;
  color: #faf8f5;
  border-radius: 999px;
  padding: 0 14px;
}

@media (min-width: 768px) {
  .topbar {
    min-height: 66px;
  }

  .icon-link,
  .left-spacer {
    width: 34px;
    height: 34px;
  }

  .icon-link {
    font-size: 24px;
  }

  .icon-link svg {
    width: 28px;
    height: 28px;
  }

  .brand-logo {
    width: 28px;
    height: 28px;
  }

  .brand-text {
    font-size: 22px;
  }

  .auth-links {
    gap: 16px;
    font-size: 12px;
    letter-spacing: 0.08em;
    min-height: 36px;
  }

  .auth-links .register {
    min-height: 36px;
    padding: 0 16px;
  }
}

@media (min-width: 1200px) {
  .brand-link {
    gap: 12px;
  }

  .brand-logo {
    width: 30px;
    height: 30px;
  }

  .brand-text {
    font-size: 24px;
  }

  .auth-links {
    gap: 18px;
    font-size: 13px;
    min-height: 38px;
  }

  .auth-links .register {
    min-height: 38px;
    padding: 0 18px;
  }
}
</style>
