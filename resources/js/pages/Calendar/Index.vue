<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import ptLocale from '@fullcalendar/core/locales/pt'

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const props = defineProps<{
    events: any[]
    users: any[]
    entities: any[]
    types: any[]
    actions: any[]
    filters?: {
        user_id?: number
        entity_id?: number
    }
}>()

/* -------------------------------------------------------------------------- */
/* STATE */
/* -------------------------------------------------------------------------- */

const showModal = ref(false)
const editingEventId = ref<number | null>(null)

const form = ref({
    title: '',
    start: '',
    end: '',
    all_day: false,
    description: '',
    user_id: null as number | null,
    entity_id: null as number | null,
    status: 'pending',
})

const filterUserId = ref<number | null>(props.filters?.user_id || null)
const filterEntityId = ref<number | null>(props.filters?.entity_id || null)

/* -------------------------------------------------------------------------- */
/* COMPUTED */
/* -------------------------------------------------------------------------- */

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: ptLocale,
    editable: true,
    selectable: true,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: props.events.map(e => ({
        id: e.id,
        title: e.title,
        start: e.start,
        end: e.end,
        allDay: e.all_day,
        backgroundColor: e.calendar_type?.color || '#3b82f6',
        borderColor: e.calendar_type?.color || '#3b82f6',
    })),
    select: onSelect,
    eventClick: onEventClick,
    eventDrop: onEventMove,
    eventResize: onEventResize,
    height: 'auto',
}))

/* -------------------------------------------------------------------------- */
/* METHODS */
/* -------------------------------------------------------------------------- */

function onSelect(info: any) {
    editingEventId.value = null
    form.value = {
        title: '',
        start: info.startStr.slice(0, 16),
        end: info.endStr ? info.endStr.slice(0, 16) : '',
        all_day: info.allDay,
        description: '',
        user_id: null,
        entity_id: null,
        status: 'pending',
    }
    showModal.value = true
}

function onEventClick(info: any) {
    const event = props.events.find(e => e.id == info.event.id)
    if (!event) return

    editingEventId.value = event.id
    form.value = {
        title: event.title,
        start: event.start.slice(0, 16),
        end: event.end ? event.end.slice(0, 16) : '',
        all_day: event.all_day,
        description: event.description || '',
        user_id: event.user_id,
        entity_id: event.entity_id,
        status: event.status,
    }
    showModal.value = true
}

function onEventMove(info: any) {
    const event = props.events.find(e => e.id == info.event.id)
    if (!event) return

    router.put(`/calendar-events/${info.event.id}`, {
        ...event,
        start: info.event.startStr,
        end: info.event.endStr,
    }, {
        preserveScroll: true,
    })
}

function onEventResize(info: any) {
    const event = props.events.find(e => e.id == info.event.id)
    if (!event) return

    router.put(`/calendar-events/${info.event.id}`, {
        ...event,
        end: info.event.endStr,
    }, {
        preserveScroll: true,
    })
}

function saveEvent() {
    if (editingEventId.value) {
        router.put(`/calendar-events/${editingEventId.value}`, form.value, {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false
            }
        })
    } else {
        router.post('/calendar-events', form.value, {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false
            }
        })
    }
}

function deleteEvent() {
    if (!editingEventId.value) return
    if (!confirm('Eliminar este evento?')) return

    router.delete(`/calendar-events/${editingEventId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false
        }
    })
}

function applyFilters() {
    router.get('/calendar-events', {
        user_id: filterUserId.value,
        entity_id: filterEntityId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

function clearFilters() {
    filterUserId.value = null
    filterEntityId.value = null
    applyFilters()
}
</script>

<template>
    <AppLayout title="Calendário">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Calendário
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Filtros</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Utilizador</label>
                            <select v-model="filterUserId"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @change="applyFilters">
                                <option :value="null">Todos</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">
                                    {{ u.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Entidade</label>
                            <select v-model="filterEntityId"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @change="applyFilters">
                                <option :value="null">Todas</option>
                                <option v-for="e in entities" :key="e.id" :value="e.id">
                                    {{ e.name }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button @click="clearFilters"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition">
                                Limpar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <FullCalendar :options="calendarOptions" />
                </div>

            </div>
        </div>

        <div v-if="showModal" class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-2xl z-50">
            <div class="mx-auto max-w-7xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ editingEventId ? 'Editar Evento' : 'Novo Evento' }}
                    </h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-gray-700 text-2xl">
                        X
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-4">

                    <div class="col-span-3">
                        <label class="block text-sm font-medium mb-1">Título *</label>
                        <input v-model="form.title" placeholder="Nome do evento..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Início *</label>
                        <input v-model="form.start" type="datetime-local"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Fim</label>
                        <input v-model="form.end" type="datetime-local"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Utilizador *</label>
                        <select v-model="form.user_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                            <option :value="null">Selecione...</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-span-3">
                        <label class="block text-sm font-medium mb-1">Entidade</label>
                        <select v-model="form.entity_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option :value="null">Nenhuma</option>
                            <option v-for="e in entities" :key="e.id" :value="e.id">
                                {{ e.name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-span-3">
                        <label class="block text-sm font-medium mb-1">Descrição</label>
                        <textarea v-model="form.description" placeholder="Detalhes do evento..." rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div class="col-span-3 flex gap-2 justify-end pt-4 border-t">
                        <button v-if="editingEventId" @click="deleteEvent"
                            class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                            Eliminar
                        </button>
                        <button @click="showModal = false"
                            class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button @click="saveEvent"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            {{ editingEventId ? 'Atualizar' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
