<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/formatMoney';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
    plans: { type: Array, default: () => [] },
    legalLinks: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Wiki — Your team's knowledge base" />

    <div class="min-h-screen bg-[#ffffff] text-[#374151]">

        <!-- Top Navigation -->
        <nav class="sticky top-0 z-50 border-b border-[#e5e7eb] bg-[#ffffff]">
            <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between px-6">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-[8px] bg-[#111111]">
                        <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="font-display text-[15px] font-700 tracking-tight text-[#111111]" style="font-family: 'Manrope', 'Inter', sans-serif; font-weight: 700;">Wiki</span>
                </div>

                <!-- Nav Links -->
                <div class="hidden items-center gap-1 md:flex">
                    <a href="#features" class="rounded-[8px] px-3 py-2 text-[14px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa] hover:text-[#111111]">Features</a>
                    <a href="#how-it-works" class="rounded-[8px] px-3 py-2 text-[14px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa] hover:text-[#111111]">How it works</a>
                    <a href="#pricing" class="rounded-[8px] px-3 py-2 text-[14px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa] hover:text-[#111111]">Pricing</a>
                </div>

                <!-- Auth Buttons -->
                <div v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-[8px] bg-[#111111] px-[20px] py-[10px] text-[14px] font-[600] leading-none text-white transition-colors hover:bg-[#242424]"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="text-[14px] font-[500] text-[#374151] transition-colors hover:text-[#111111]"
                        >
                            Sign in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-[8px] bg-[#111111] px-[20px] py-[10px] text-[14px] font-[600] leading-none text-white transition-colors hover:bg-[#242424]"
                        >
                            Get started free
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Band -->
        <section class="mx-auto max-w-[1200px] px-6 py-24">
            <div class="mx-auto max-w-[720px] text-center">
                <!-- Badge -->
                <div class="mb-6 inline-flex items-center gap-2 rounded-[9999px] bg-[#f5f5f5] px-[12px] py-[4px]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#10b981]"></span>
                    <span class="text-[13px] font-[500] text-[#374151]">Now in open beta</span>
                </div>

                <!-- Display Heading -->
                <h1
                    style="font-family: 'Manrope', 'Inter', sans-serif; font-weight: 700; font-size: 56px; line-height: 1.05; letter-spacing: -2px; color: #111111;"
                    class="mb-6"
                >
                    The smarter way to build your team's knowledge base
                </h1>

                <p class="mb-10 text-[18px] leading-relaxed text-[#6b7280]">
                    Create, organize, and share documentation your whole team will actually use. Wiki keeps everyone aligned — from onboarding to deep technical specs.
                </p>

                <!-- CTA Row -->
                <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="inline-flex h-[44px] items-center rounded-[8px] bg-[#111111] px-[24px] text-[14px] font-[600] text-white transition-colors hover:bg-[#242424]"
                    >
                        Start for free
                    </Link>
                    <a
                        href="#features"
                        class="inline-flex h-[44px] items-center rounded-[8px] border border-[#e5e7eb] bg-white px-[24px] text-[14px] font-[600] text-[#111111] transition-colors hover:bg-[#f8f9fa]"
                    >
                        See how it works
                    </a>
                </div>

                <p class="mt-4 text-[13px] text-[#898989]">Free forever for up to 5 team members. No credit card required.</p>
            </div>

            <!-- Hero mockup card -->
            <div class="mt-16 overflow-hidden rounded-[16px] border border-[#e5e7eb] bg-[#f5f5f5] shadow-[0_4px_12px_rgba(0,0,0,0.08)]">
                <div class="border-b border-[#e5e7eb] bg-white px-4 py-3 flex items-center gap-2">
                    <div class="h-3 w-3 rounded-full bg-[#e5e7eb]"></div>
                    <div class="h-3 w-3 rounded-full bg-[#e5e7eb]"></div>
                    <div class="h-3 w-3 rounded-full bg-[#e5e7eb]"></div>
                    <div class="mx-auto w-64 rounded-[6px] bg-[#f5f5f5] px-3 py-1 text-center text-[12px] text-[#898989]">wiki.yourteam.com</div>
                </div>
                <div class="grid grid-cols-5 min-h-[360px]">
                    <!-- Sidebar -->
                    <div class="col-span-1 border-r border-[#e5e7eb] bg-white p-4">
                        <div class="mb-3 text-[11px] font-[600] uppercase tracking-wider text-[#898989]">Pages</div>
                        <div class="space-y-1">
                            <div class="rounded-[6px] bg-[#f5f5f5] px-2 py-1.5 text-[13px] font-[500] text-[#111111]">Getting Started</div>
                            <div class="px-2 py-1.5 text-[13px] text-[#6b7280]">Architecture</div>
                            <div class="px-2 py-1.5 text-[13px] text-[#6b7280]">API Reference</div>
                            <div class="px-2 py-1.5 text-[13px] text-[#6b7280]">Deployment</div>
                            <div class="px-2 py-1.5 text-[13px] text-[#6b7280]">Contributing</div>
                        </div>
                    </div>
                    <!-- Content area -->
                    <div class="col-span-4 bg-white p-8">
                        <div class="mb-2 text-[11px] font-[500] text-[#898989]">Getting Started → Overview</div>
                        <h2 style="font-family: 'Manrope', sans-serif; font-weight: 700; font-size: 24px; letter-spacing: -0.5px; color: #111111;" class="mb-4">Welcome to the team</h2>
                        <div class="space-y-3">
                            <div class="h-3 w-full rounded-full bg-[#f5f5f5]"></div>
                            <div class="h-3 w-5/6 rounded-full bg-[#f5f5f5]"></div>
                            <div class="h-3 w-4/6 rounded-full bg-[#f5f5f5]"></div>
                        </div>
                        <div class="mt-6 rounded-[8px] bg-[#f5f5f5] p-4">
                            <div class="mb-2 h-2.5 w-24 rounded-full bg-[#e5e7eb]"></div>
                            <div class="space-y-2">
                                <div class="h-2.5 w-full rounded-full bg-[#e5e7eb]"></div>
                                <div class="h-2.5 w-3/4 rounded-full bg-[#e5e7eb]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature Cards - 3-up grid -->
        <section id="features" class="bg-[#f8f9fa] py-24">
            <div class="mx-auto max-w-[1200px] px-6">
                <div class="mb-4 text-center">
                    <span class="text-[13px] font-[500] text-[#898989] uppercase tracking-wider">Features</span>
                </div>
                <h2
                    style="font-family: 'Manrope', 'Inter', sans-serif; font-weight: 700; font-size: 36px; line-height: 1.15; letter-spacing: -1px; color: #111111;"
                    class="mb-4 text-center"
                >
                    Everything your team needs to stay aligned
                </h2>
                <p class="mb-16 text-center text-[16px] text-[#6b7280]">Built for teams that care about documentation. Simple enough for anyone, powerful enough for engineers.</p>

                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Card 1 -->
                    <div class="rounded-[12px] bg-[#f5f5f5] p-8">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-[8px] bg-[#111111]">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <h3 class="mb-2 text-[18px] font-[600] text-[#111111]">Rich editing experience</h3>
                        <p class="text-[15px] leading-relaxed text-[#6b7280]">Write with a clean, distraction-free editor that supports Markdown, code blocks, tables, and embedded media out of the box.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="rounded-[12px] bg-[#f5f5f5] p-8">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-[8px] bg-[#111111]">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h3 class="mb-2 text-[18px] font-[600] text-[#111111]">Instant full-text search</h3>
                        <p class="text-[15px] leading-relaxed text-[#6b7280]">Find any page, section, or code snippet in milliseconds. Full-text search across your entire knowledge base with ranked results.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="rounded-[12px] bg-[#f5f5f5] p-8">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-[8px] bg-[#111111]">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h3 class="mb-2 text-[18px] font-[600] text-[#111111]">Granular permissions</h3>
                        <p class="text-[15px] leading-relaxed text-[#6b7280]">Owner, Admin, and User roles control who can view, edit, or manage content. Invite teammates and assign roles from workspace settings.</p>
                    </div>

                    <!-- Card 4 -->
                    <div class="rounded-[12px] bg-[#f5f5f5] p-8">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-[8px] bg-[#111111]">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <h3 class="mb-2 text-[18px] font-[600] text-[#111111]">Version history</h3>
                        <p class="text-[15px] leading-relaxed text-[#6b7280]">Every change is tracked automatically. Browse the full edit history for any page and restore any version with a single click.</p>
                    </div>

                    <!-- Card 5 -->
                    <div class="rounded-[12px] bg-[#f5f5f5] p-8">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-[8px] bg-[#111111]">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <h3 class="mb-2 text-[18px] font-[600] text-[#111111]">Integrations</h3>
                        <p class="text-[15px] leading-relaxed text-[#6b7280]">Connect MinIO archive, GlitchTip error tracking, and AI providers. Available on Pro and above.</p>
                    </div>

                    <!-- Card 6 -->
                    <div class="rounded-[12px] bg-[#f5f5f5] p-8">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-[8px] bg-[#111111]">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h3 class="mb-2 text-[18px] font-[600] text-[#111111]">Roles & audit trail</h3>
                        <p class="text-[15px] leading-relaxed text-[#6b7280]">Owner, Admin, and User roles with granular permissions. Team plan adds a full activity log and custom role editing.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing -->
        <section id="pricing" class="py-24">
            <div class="mx-auto max-w-[1200px] px-6">
                <div class="mb-10 text-center">
                    <h2 class="text-[36px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Simple pricing</h2>
                    <p class="mt-3 text-[16px] text-[#6b7280]">Start free. Upgrade when your team grows.</p>
                </div>

                <div class="grid items-stretch gap-6 md:grid-cols-3">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        class="flex h-full flex-col rounded-[12px] border bg-white p-8"
                        :class="plan.is_popular ? 'border-[#111111] shadow-sm' : 'border-[#e5e7eb]'"
                    >
                        <div class="mb-3 min-h-[22px]">
                            <span v-if="plan.is_popular" class="inline-block rounded-[9999px] bg-[#111111] px-2.5 py-0.5 text-[11px] font-[600] text-white">Popular</span>
                        </div>
                        <h3 class="text-[20px] font-[700] text-[#111111]">{{ plan.name }}</h3>
                        <p class="mt-2 text-[32px] font-[700] text-[#111111]">{{ formatPrice(plan.price_monthly_cents, plan.currency) }}<span v-if="plan.price_monthly_cents" class="text-[14px] font-[500] text-[#6b7280]">/mo</span></p>
                        <p class="mt-2 text-[14px] text-[#6b7280]">{{ plan.description }}</p>
                        <ul class="mt-6 flex-1 space-y-2 text-[14px] text-[#374151]">
                            <li v-for="(feature, i) in plan.features" :key="i">✓ {{ feature }}</li>
                        </ul>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="mt-8 inline-flex h-[44px] w-full shrink-0 items-center justify-center rounded-[8px] text-[14px] font-[600] transition-colors"
                            :class="plan.is_popular ? 'bg-[#111111] text-white hover:bg-[#242424]' : 'border border-[#e5e7eb] text-[#111111] hover:bg-[#f8f9fa]'"
                        >
                            Get started
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-[#101010] px-6 py-16">
            <div class="mx-auto max-w-[1200px]">
                <div class="mb-12 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-[8px] bg-[#1a1a1a]">
                        <svg class="h-4 w-4 text-[#a1a1aa]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span style="font-family: 'Manrope', sans-serif; font-weight: 700;" class="text-[15px] text-white">Wiki</span>
                </div>

                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div>
                        <div class="mb-4 text-[13px] font-[600] text-white">Product</div>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Features</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Pricing</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Changelog</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Roadmap</a></li>
                        </ul>
                    </div>
                    <div>
                        <div class="mb-4 text-[13px] font-[600] text-white">Solutions</div>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Engineering</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Product teams</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Startups</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Enterprise</a></li>
                        </ul>
                    </div>
                    <div>
                        <div class="mb-4 text-[13px] font-[600] text-white">Resources</div>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Documentation</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Blog</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Community</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Status</a></li>
                        </ul>
                    </div>
                    <div>
                        <div class="mb-4 text-[13px] font-[600] text-white">Company</div>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">About</a></li>
                            <li><a href="#" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">Careers</a></li>
                            <li v-for="link in legalLinks" :key="link.slug">
                                <Link :href="route('legal.show', link.slug)" class="text-[14px] text-[#a1a1aa] transition-colors hover:text-white">{{ link.label }}</Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-16 border-t border-[#1a1a1a] pt-8 text-[13px] text-[#898989]">
                    © {{ new Date().getFullYear() }} Wiki. Built with Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
                </div>
            </div>
        </footer>
    </div>
</template>
