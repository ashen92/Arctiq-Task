<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

interface Task {
  id: number;
  title: string;
  description: string;
  status: TaskStatus;
}

enum TaskStatus {
  PENDING = 'pending',
  COMPLETED = 'completed'
}

interface PaginationData {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

const tasks = ref<Task[]>([]);
const newTask = ref({ title: '', description: '', status: TaskStatus.PENDING });
const editingTask = ref<Task | null>(null);
const currentFilter = ref('all');
const pagination = ref<PaginationData | null>(null);
const currentPage = ref(1);

const fetchTasks = async (page = 1) => {
  try {
    const response = await axios.get(`/tasks/filter/${currentFilter.value}`, {
      params: { page }
    });
    tasks.value = response.data.data;
    pagination.value = response.data.meta;
    currentPage.value = page;
  } catch (error) {
    console.error('Error fetching tasks:', error);
  }
};

const createTask = async () => {
  try {
    await axios.post('/tasks', newTask.value);
    newTask.value = { title: '', description: '', status: TaskStatus.PENDING };
    await fetchTasks(currentPage.value);
  } catch (error) {
    console.error('Error creating task:', error);
  }
};

const startEditing = (task: Task) => {
  editingTask.value = { ...task };
};

const cancelEditing = () => {
  editingTask.value = null;
};

const updateTask = async (task: Task) => {
  try {
    await axios.put(`/tasks/${task.id}`, task);
    editingTask.value = null;
    await fetchTasks(currentPage.value);
  } catch (error) {
    console.error('Error updating task:', error);
  }
};

const deleteTask = async (id: number) => {
  try {
    await axios.delete(`/tasks/${id}`);
    await fetchTasks(currentPage.value);
  } catch (error) {
    console.error('Error deleting task:', error);
  }
};

const changePage = (page: number) => {
  fetchTasks(page);
};

watch(currentFilter, () => fetchTasks(1));

onMounted(() => fetchTasks(1));
</script>

<template>
  <div class="task-manager">
    <h3 class="text-lg font-semibold mb-4">Your Tasks</h3>
    
    <!-- Filter Dropdown -->
    <div class="mb-4">
      <label for="status-filter" class="mr-2">Filter by status:</label>
      <select id="status-filter" v-model="currentFilter" class="border p-2">
        <option value="all">All</option>
        <option :value="TaskStatus.PENDING">Pending</option>
        <option :value="TaskStatus.COMPLETED">Completed</option>
      </select>
    </div>

    <!-- Create Task Form -->
    <form @submit.prevent="createTask" class="mb-6">
      <input v-model="newTask.title" placeholder="Task title" required class="border p-2 mr-2">
      <input v-model="newTask.description" placeholder="Description" class="border p-2 mr-2">
      <select v-model="newTask.status" class="border p-2 mr-2">
        <option :value="TaskStatus.PENDING">Pending</option>
        <option :value="TaskStatus.COMPLETED">Completed</option>
      </select>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Task</button>
    </form>

    <!-- Task List -->
    <ul>
      <li v-for="task in tasks" :key="task.id" class="mb-4 p-4 border rounded">
        <div v-if="editingTask?.id !== task.id" class="flex justify-between items-start">
          <div>
            <h4 class="font-bold">{{ task.title }}</h4>
            <p>{{ task.description }}</p>
            <p>Status: {{ task.status }}</p>
          </div>
          <div>
            <button @click="startEditing(task)" class="text-blue-500 mr-2">Edit</button>
            <button @click="deleteTask(task.id)" class="text-red-500">Delete</button>
          </div>
        </div>
        <form v-else @submit.prevent="updateTask(editingTask)" class="space-y-2">
          <input v-model="editingTask.title" required class="border p-2 w-full">
          <input v-model="editingTask.description" class="border p-2 w-full">
          <select v-model="editingTask.status" class="border p-2 w-full">
            <option :value="TaskStatus.PENDING">Pending</option>
            <option :value="TaskStatus.COMPLETED">Completed</option>
          </select>
          <div class="flex justify-end space-x-2">
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Save</button>
            <button @click="cancelEditing" type="button" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
          </div>
        </form>
      </li>
    </ul>

    <!-- Pagination -->
    <div v-if="pagination" class="mt-4 flex justify-center space-x-2">
      <button 
        @click="changePage(currentPage - 1)" 
        :disabled="currentPage === 1"
        class="px-4 py-2 border rounded"
        :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
      >
        Previous
      </button>
      <span class="px-4 py-2">Page {{ currentPage }} of {{ pagination.last_page }}</span>
      <button 
        @click="changePage(currentPage + 1)" 
        :disabled="currentPage === pagination.last_page"
        class="px-4 py-2 border rounded"
        :class="{ 'opacity-50 cursor-not-allowed': currentPage === pagination.last_page }"
      >
        Next
      </button>
    </div>
  </div>
</template>