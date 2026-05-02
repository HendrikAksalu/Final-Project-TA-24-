<script setup>
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
    <div v-else class="left-spacer" aria-hidden="true"></div>

    <div class="brand-wrap">
      <img class="brand-logo" src="/logo.png" alt="Fototeek logo" />
      <span class="brand-text">Fototeek</span>
    </div>

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
  display: flex;
  align-items: center;
  justify-content: space-between;
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
}

.icon-link svg {
  width: 28px;
  height: 28px;
}

.left-spacer {
  width: 32px;
  height: 32px;
}

.brand-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-logo {
  width: 22px;
  height: 22px;
  object-fit: contain;
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
}

.auth-links a {
  color: var(--ink, #231f20);
  text-decoration: none;
}

.auth-links .register {
  background: #231f20;
  color: #faf8f5;
  border-radius: 999px;
  padding: 8px 14px;
}

@media (min-width: 768px) {
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
    width: 24px;
    height: 24px;
  }

  .brand-text {
    font-size: 22px;
  }

  .auth-links {
    gap: 16px;
    font-size: 12px;
    letter-spacing: 0.08em;
  }

  .auth-links .register {
    padding: 9px 16px;
  }
}

@media (min-width: 1200px) {
  .brand-wrap {
    gap: 12px;
  }

  .brand-logo {
    width: 26px;
    height: 26px;
  }

  .brand-text {
    font-size: 24px;
  }

  .auth-links {
    gap: 18px;
    font-size: 13px;
  }

  .auth-links .register {
    padding: 10px 18px;
  }
}
</style>
