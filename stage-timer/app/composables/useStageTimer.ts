// composables/useStageTimer.ts
import { ref, onMounted, onUnmounted } from 'vue'

export const useStageTimer = (isController: boolean = false) => {
    const timeLeft = ref(0)
    const totalTime = ref(0)
    const isRunning = ref(false)
    const isChillOut = ref(false)

    let channel: BroadcastChannel | null = null
    let timerInterval: ReturnType<typeof setInterval> | null = null

    onMounted(() => {
        if (import.meta.client) {
            channel = new BroadcastChannel('stage_timer_channel')

            if (isController) {
                channel.onmessage = (event) => {
                    if (event.data.type === 'REQUEST_SYNC') {
                        broadcastState()
                    }
                }
            } else {
                channel.onmessage = (event) => {
                    if (event.data.type === 'SYNC_STATE') {
                        timeLeft.value = event.data.timeLeft
                        totalTime.value = event.data.totalTime
                        isRunning.value = event.data.isRunning
                        isChillOut.value = event.data.isChillOut
                    }
                }
                channel.postMessage({ type: 'REQUEST_SYNC' })
            }
        }
    })

    const broadcastState = () => {
        if (channel && isController) {
            channel.postMessage({
                type: 'SYNC_STATE',
                timeLeft: timeLeft.value,
                totalTime: totalTime.value,
                isRunning: isRunning.value,
                isChillOut: isChillOut.value
            })
        }
    }

    const setTime = (seconds: number) => {
        if (!isController) return
        timeLeft.value = seconds
        totalTime.value = seconds
        isRunning.value = false
        isChillOut.value = false
        stopTicking()
        broadcastState()
    }

    const toggleTimer = () => {
        if (!isController) return
        isRunning.value = !isRunning.value
        isChillOut.value = false

        if (isRunning.value) {
            startTicking()
        } else {
            stopTicking()
        }
        broadcastState()
    }

    const toggleChillOut = () => {
        if (!isController) return
        isChillOut.value = !isChillOut.value
        isRunning.value = false
        stopTicking()
        broadcastState()
    }

    const startTicking = () => {
        if (timerInterval) clearInterval(timerInterval)
        timerInterval = setInterval(() => {
            if (timeLeft.value > 0) {
                timeLeft.value--
                broadcastState()
            } else {
                stopTicking()
                isRunning.value = false
                broadcastState()
            }
        }, 1000)
    }

    const stopTicking = () => {
        if (timerInterval) clearInterval(timerInterval)
    }

    onUnmounted(() => {
        stopTicking()
        if (channel) channel.close()
    })

    return {
        timeLeft,
        totalTime,
        isRunning,
        isChillOut,
        setTime,
        toggleTimer,
        toggleChillOut
    }
}