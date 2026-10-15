<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-[#f5f1e8] text-[#242820]">
    <header class="border-b border-[#242820]/20">
      <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5 sm:px-8">
        <RouterLink to="/" class="flex items-center gap-3" aria-label="BookFlow, home">
          <span class="grid size-10 place-items-center bg-[#d45b3e] text-[#f5f1e8]">
            <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
              <path
                d="M12 6.5C9.5 4.8 6.4 4.5 3 5.5v13c3.4-1 6.5-.7 9 1 2.5-1.7 5.6-2 9-1v-13c-3.4-1-6.5-.7-9 1Zm0 0v13"
                stroke="currentColor"
                stroke-width="1.6"
                stroke-linecap="square"
              />
            </svg>
          </span>
          <span class="text-xl font-black tracking-tight">Bookflow</span>
        </RouterLink>

        <div class="flex items-center gap-5 text-sm font-semibold sm:gap-8">
          <RouterLink to="/login" class="hidden transition-colors hover:text-[#d45b3e] sm:block">
            Accedi
          </RouterLink>
          <RouterLink
            to="/register"
            class="border border-[#242820] bg-[#d45b3e] px-4 py-2.5 text-[#f5f1e8] uppercase font-bold transition-colors hover:bg-[#242820] hover:border-[#d45b3e] sm:px-5"
          >
            Diventa socio
          </RouterLink>
        </div>
      </nav>
    </header>

    <main>
      <section
        class="mx-auto grid max-w-6xl items-center gap-12 px-5 py-16 sm:px-8 sm:py-24 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16 lg:py-28"
      >
        <div>
          <p
            class="mb-6 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-[#64704a]"
          >
            <span class="size-2 bg-[#d45b3e]"></span>
            La biblioteca fatta dalle persone
          </p>
          <h1 class="max-w-2xl text-5xl font-black leading-[0.98] tracking-[-0.06em] sm:text-7xl">
            Le storie più belle
            <span class="font-serif font-normal italic text-[#d45b3e]">passano di mano.</span>
          </h1>
          <p class="mt-7 max-w-xl text-lg font-medium leading-8 text-[#55594e]">
            BookFlow è una biblioteca sociale: un posto dove i libri si scambiano, si prendono in
            prestito e continuano il loro viaggio.
          </p>
          <div class="mt-9 flex flex-wrap items-center gap-4">
            <RouterLink
              to="/register"
              class="inline-flex items-center gap-5 border border-[#d45b3e] bg-[#d45b3e] px-6 py-4 text-sm font-bold text-white transition-colors hover:border-[#242820] hover:bg-[#242820]"
            >
              Entra nella community
            </RouterLink>
            <RouterLink
              to="/login"
              class="px-2 py-3 text-sm font-bold underline decoration-[#d45b3e] decoration-2 underline-offset-4 hover:text-[#d45b3e]"
            >
              Sono già socio
            </RouterLink>
          </div>
          <p class="mt-8 text-xs font-medium uppercase tracking-[0.12em] text-[#74766d]">
            Registrati, scegli un libro, condividi la tua prossima lettura.
          </p>
        </div>

        <div
          class="relative mx-auto w-full max-w-lg border border-[#242820] bg-[#e8dfcd] p-5 sm:p-8"
          aria-label="Illustrazione di libri da condividere"
        >
          <div
            class="flex items-center justify-between border-b border-[#242820]/25 pb-4 text-[10px] font-bold uppercase tracking-[0.18em]"
          >
            <span>Una storia alla volta</span>
          </div>
          <div class="relative flex h-72 items-end justify-center gap-2 py-7 sm:h-80">
            <div
              class="flex h-[100%] w-[24%] items-center justify-center border border-[#242820] bg-[#64704a]"
            >
              <span class="font-serif text-xl italic text-[#f5f1e8] [writing-mode:vertical-rl]"
                >Ogni momento è giusto</span
              >
            </div>
            <div
              class="flex h-[100%] w-[40%] flex-col justify-between border border-[#242820] bg-[#d45b3e] p-3 text-[#f5f1e8] sm:p-4"
            >
              <span class="font-serif text-3xl leading-none italic sm:text-4xl"
                >Per leggere<br />insieme.</span
              >
              <span class="text-[9px] font-bold uppercase tracking-[0.16em]"
                >Un libro, mille vite</span
              >
            </div>
            <div
              class="flex h-[100%] w-[24%] items-center justify-center border border-[#242820] bg-[#d5ad56]"
            >
              <span class="font-serif text-xl italic text-[#242820] [writing-mode:vertical-rl]"
                >Storie da raccontare</span
              >
            </div>
          </div>
          <div
            class="flex items-center justify-between border-t border-[#242820]/25 pt-4 text-xs font-semibold"
          >
            <span>Prendi. Leggi. Passa.</span>
          </div>
        </div>
      </section>

      <section class="border-y border-[#242820]/20 bg-[#ede7da]">
        <div
          class="mx-auto grid max-w-6xl gap-8 px-5 py-14 sm:px-8 md:grid-cols-[0.8fr_1.2fr] md:items-center md:py-16"
        >
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d45b3e]">
            Più di una biblioteca
          </p>
          <p class="max-w-3xl text-2xl font-semibold leading-snug tracking-tight sm:text-3xl">
            Qui ogni libro è un incontro.
            <span class="font-normal text-[#65675e]"
              >I soci condividono le proprie letture e fanno spazio a quelle degli altri.</span
            >
          </p>
        </div>
      </section>

      <section class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
        <div class="mb-10 flex flex-col justify-between gap-4 sm:mb-12 sm:flex-row sm:items-end">
          <div>
            <p class="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-[#64704a]">
              Semplice, come dovrebbe essere
            </p>
            <h2 class="text-3xl font-black tracking-tight sm:text-4xl">
              Il bello di far girare i libri.
            </h2>
          </div>
          <p class="max-w-sm font-medium leading-7 text-[#65675e]">
            Unisciti ai soci e trasforma la tua prossima lettura in una storia da condividere.
          </p>
        </div>

        <div class="grid border-l border-t border-[#242820]/25 sm:grid-cols-3">
          <article class="border-b border-r border-[#242820]/25 p-6 sm:p-8 bg-[#ede7da]">
            <span class="font-serif text-4xl italic text-[#d45b3e]">01</span>
            <h3 class="mt-7 text-xl font-bold">Registrati come socio</h3>
            <p class="mt-3 leading-7 text-[#65675e]">
              Crea il tuo account e diventa parte di una comunità che ama leggere e condividere.
            </p>
          </article>
          <article class="border-b border-r border-[#242820]/25 p-6 sm:p-8 bg-[#ede7da]">
            <span class="font-serif text-4xl italic text-[#d45b3e]">02</span>
            <h3 class="mt-7 text-xl font-bold">Scegli il tuo prossimo libro</h3>
            <p class="mt-3 leading-7 text-[#65675e]">
              Esplora i titoli disponibili e chiedi in prestito la storia che ti incuriosisce.
            </p>
          </article>
          <article class="border-b border-r border-[#242820]/25 p-6 sm:p-8 bg-[#ede7da]">
            <span class="font-serif text-4xl italic text-[#d45b3e]">03</span>
            <h3 class="mt-7 text-xl font-bold">Scambia e rimetti in circolo</h3>
            <p class="mt-3 leading-7 text-[#65675e]">
              Condividi i tuoi libri con gli altri soci: ogni passaggio apre una nuova avventura.
            </p>
          </article>
        </div>
      </section>

      <section class="bg-[#242820] text-[#f5f1e8]">
        <div
          class="mx-auto flex max-w-6xl flex-col gap-7 px-5 py-14 sm:px-8 sm:py-16 md:flex-row md:items-center md:justify-between"
        >
          <div>
            <p class="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-[#d5ad56]">
              La prossima pagina è tua
            </p>
            <h2 class="max-w-2xl text-3xl font-black tracking-tight sm:text-4xl">
              Entra in BookFlow!
            </h2>
          </div>
          <RouterLink
            to="/register"
            class="inline-flex shrink-0 items-center justify-between gap-8 border border-[#d5ad56] px-6 py-4 text-lg font-bold text-[#d5ad56] transition-colors hover:bg-[#d5ad56] hover:text-[#242820]"
          >
            Registrati gratuitamente
          </RouterLink>
        </div>
      </section>
    </main>

    <footer class="border-t border-[#242820]/20">
      <div
        class="mx-auto text-center space-x-3 max-w-6xl px-5 py-6 text-xs text-[#65675e] sm:px-8"
      >
        <span class="font-black tracking-tight text-[#242820]">Bookflow</span>
        <span>Una biblioteca sociale, fatta per essere condivisa.</span>
      </div>
    </footer>
  </div>
</template>

 
