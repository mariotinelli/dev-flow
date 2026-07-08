<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ChevronsUpDown, Copy as CopyIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import CopyController from '@/actions/App/Http/Controllers/ProjectSettings/ProjectRoles/CopyController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import type { ProjectRoleSourceProject } from '@/types';

const props = defineProps<{
    sourceProjects: ProjectRoleSourceProject[];
}>();

const isOpen = ref(false);
const projectOpen = ref(false);

const form = useForm({
    source_project_id: '',
});

const selectedProject = computed(() =>
    props.sourceProjects.find((project) => String(project.id) === form.source_project_id),
);

function submit(): void {
    form.post(CopyController.url(), {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
        },
    });
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button type="button" variant="outline" class="gap-2" :disabled="sourceProjects.length === 0">
                <CopyIcon class="size-4" />
                Copiar papéis
            </Button>
        </DialogTrigger>

        <DialogContent>
            <form class="space-y-6" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Copiar papéis de outro projeto</DialogTitle>
                    <DialogDescription>
                        Selecione um projeto de origem. Papéis com o mesmo nome serão atualizados no projeto atual.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="source-project" required>Projeto de origem</Label>
                    <Popover v-model:open="projectOpen">
                        <PopoverTrigger as-child>
                            <Button
                                id="source-project"
                                type="button"
                                variant="outline"
                                role="combobox"
                                :aria-expanded="projectOpen"
                                class="w-full justify-between"
                            >
                                <span class="truncate">
                                    {{ selectedProject ? `${selectedProject.name} (${selectedProject.key})` : 'Selecione um projeto' }}
                                </span>

                                <ChevronsUpDown class="size-4 shrink-0 opacity-50" />
                            </Button>
                        </PopoverTrigger>

                        <PopoverContent class="w-(--reka-popover-trigger-width) p-0">
                            <Command>
                                <CommandInput placeholder="Pesquisar projeto..." />

                                <CommandList>
                                    <CommandEmpty>Nenhum projeto encontrado.</CommandEmpty>

                                    <CommandGroup>
                                        <CommandItem
                                            v-for="project in sourceProjects"
                                            :key="project.id"
                                            :value="`${project.name} ${project.key}`"
                                            @select="
                                                form.source_project_id = String(project.id);
                                                projectOpen = false;
                                            "
                                        >
                                            <div class="flex min-w-0 flex-col">
                                                <span class="truncate">{{ project.name }} ({{ project.key }})</span>
                                                <span class="text-xs text-muted-foreground">
                                                    {{ project.roles_count }} papéis disponíveis
                                                </span>
                                            </div>
                                        </CommandItem>
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>
                    <InputError :message="form.errors.source_project_id" />
                </div>

                <DialogFooter class="gap-2">
                    <Button type="button" variant="outline" @click="isOpen = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing">Copiar papéis</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
