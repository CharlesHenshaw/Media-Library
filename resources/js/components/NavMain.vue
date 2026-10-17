<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();

// Active on the item's page and any filtered view of it, e.g. /library?type=book.
const isActive = (item: NavItem) => page.url === item.href || page.url.startsWith(`${item.href}?`) || page.url.startsWith(`${item.href}/`);
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel class="uppercase tracking-[0.14em] text-sidebar-foreground/50">Collection</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="isActive(item)">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
