import { ref, onUnmounted, Ref } from 'vue';

export interface AudioAnalyzerData {
    leftLevel: number;
    rightLevel: number;
    frequencyData: Uint8Array;
    averageFrequency: number;
}

export function useAudioAnalyzer(audioElement: Ref<HTMLAudioElement | null>) {
    const isAnalyzing = ref(false);
    const analyzerData = ref<AudioAnalyzerData>({
        leftLevel: 0,
        rightLevel: 0,
        frequencyData: new Uint8Array(128),
        averageFrequency: 0
    });

    let audioContext: AudioContext | null = null;
    let analyzerNode: AnalyserNode | null = null;
    let splitter: ChannelSplitterNode | null = null;
    let leftAnalyzer: AnalyserNode | null = null;
    let rightAnalyzer: AnalyserNode | null = null;
    let sourceNode: MediaElementAudioSourceNode | null = null;
    let animationFrameId: number | null = null;

    const startAnalyzing = async () => {
        if (!audioElement.value || isAnalyzing.value) {
            return;
        }

        try {
            // Create audio context
            audioContext = new AudioContext();

            // Create source node from audio element
            sourceNode = audioContext.createMediaElementSource(audioElement.value);

            // Create main analyzer for frequency data
            analyzerNode = audioContext.createAnalyser();
            analyzerNode.fftSize = 256;
            analyzerNode.smoothingTimeConstant = 0.8;

            // Create channel splitter for left/right VU meters
            splitter = audioContext.createChannelSplitter(2);
            leftAnalyzer = audioContext.createAnalyser();
            leftAnalyzer.fftSize = 256;
            rightAnalyzer = audioContext.createAnalyser();
            rightAnalyzer.fftSize = 256;

            // Connect nodes
            sourceNode.connect(analyzerNode);
            sourceNode.connect(splitter);
            splitter.connect(leftAnalyzer, 0);
            splitter.connect(rightAnalyzer, 1);

            // Connect to destination (speakers)
            analyzerNode.connect(audioContext.destination);

            isAnalyzing.value = true;

            // Start animation loop
            analyze();
        } catch (error) {
            console.error('Error starting audio analyzer:', error);
            stopAnalyzing();
        }
    };

    const analyze = () => {
        if (!isAnalyzing.value || !analyzerNode || !leftAnalyzer || !rightAnalyzer) {
            return;
        }

        // Get frequency data
        const bufferLength = analyzerNode.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);
        analyzerNode.getByteFrequencyData(dataArray);

        // Calculate average frequency
        const average = dataArray.reduce((sum, value) => sum + value, 0) / bufferLength;

        // Get left channel level
        const leftData = new Uint8Array(leftAnalyzer.frequencyBinCount);
        leftAnalyzer.getByteFrequencyData(leftData);
        const leftLevel = leftData.reduce((sum, value) => sum + value, 0) / leftData.length;

        // Get right channel level
        const rightData = new Uint8Array(rightAnalyzer.frequencyBinCount);
        rightAnalyzer.getByteFrequencyData(rightData);
        const rightLevel = rightData.reduce((sum, value) => sum + value, 0) / rightData.length;

        // Update reactive data
        analyzerData.value = {
            leftLevel: Math.min(100, (leftLevel / 255) * 100),
            rightLevel: Math.min(100, (rightLevel / 255) * 100),
            frequencyData: dataArray,
            averageFrequency: average
        };

        // Continue loop
        animationFrameId = requestAnimationFrame(analyze);
    };

    const stopAnalyzing = () => {
        if (animationFrameId !== null) {
            cancelAnimationFrame(animationFrameId);
            animationFrameId = null;
        }

        if (sourceNode) {
            sourceNode.disconnect();
            sourceNode = null;
        }

        if (analyzerNode) {
            analyzerNode.disconnect();
            analyzerNode = null;
        }

        if (splitter) {
            splitter.disconnect();
            splitter = null;
        }

        if (leftAnalyzer) {
            leftAnalyzer.disconnect();
            leftAnalyzer = null;
        }

        if (rightAnalyzer) {
            rightAnalyzer.disconnect();
            rightAnalyzer = null;
        }

        if (audioContext && audioContext.state !== 'closed') {
            audioContext.close();
            audioContext = null;
        }

        isAnalyzing.value = false;
        analyzerData.value = {
            leftLevel: 0,
            rightLevel: 0,
            frequencyData: new Uint8Array(128),
            averageFrequency: 0
        };
    };

    // Clean up on unmount
    onUnmounted(() => {
        stopAnalyzing();
    });

    return {
        isAnalyzing,
        analyzerData,
        startAnalyzing,
        stopAnalyzing
    };
}
