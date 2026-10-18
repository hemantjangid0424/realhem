<template>
    <div class="p-8 text-center text-slate-500">Redirecting to recent activity...</div>
</template>

<script setup>
const router = useRouter();
onMounted(() => {
    router.replace('/recent-activity');
});
</script>

