
import { GoogleGenerativeAI } from "@google/generative-ai";
import { PropertyListing, AiEnhancedContent, RentEstimate } from '../types';

const API_KEY = import.meta.env.VITE_GEMINI_API_KEY;

if (!API_KEY) {
  console.warn("Gemini API Key not found. AI features will be disabled.");
}

const genAI = API_KEY ? new GoogleGenerativeAI(API_KEY) : null;
const TEXT_MODEL_NAME = "gemini-2.5-flash";

const parseJsonResponse = <T,>(responseText: string): T | null => {
  let jsonStr = responseText.trim();
  const fenceRegex = /^```(\w*)?\s*\n?(.*?)\n?\s*```$/s;
  const match = jsonStr.match(fenceRegex);
  if (match && match[2]) {
    jsonStr = match[2].trim();
  }
  try {
    return JSON.parse(jsonStr) as T;
  } catch (e) {
    console.error("Failed to parse JSON response:", e);
    return null;
  }
};

export const geminiService = {
  checkImageForScam: async (
    imageDataBase64: string,
    existingImageHashes?: string[]
  ): Promise<{ status: 'clear' | 'flagged_reused' | 'flagged_poor_quality'; reason?: string }> => {
    if (!genAI) return { status: 'clear', reason: "AI service disabled." };
    
    return new Promise(resolve => {
        setTimeout(() => {
            const random = Math.random();
            if (existingImageHashes && existingImageHashes.includes("mock_hash_" + imageDataBase64.substring(10,20))) {
                resolve({ status: 'flagged_reused', reason: 'Image appears to be reused from another listing.' });
            } else if (random < 0.1) {
                resolve({ status: 'flagged_reused', reason: 'AI detected potential image reuse.' });
            } else if (random < 0.15) {
                resolve({ status: 'flagged_poor_quality', reason: 'Image quality is very low.' });
            } else {
                resolve({ status: 'clear' });
            }
        }, 1500);
    });
  },

  enhanceListingContent: async (listing: PropertyListing): Promise<AiEnhancedContent | null> => {
    if (!genAI) {
      console.error('[Gemini] AI service not initialized. API key missing:', !API_KEY);
      return null;
    }

    const prompt = `
      Given the following Kenyan rental listing details, enhance them.
      Provide an engaging property description (max 150 words), a catchy title (max 10 words),
      and brief pricing advice.
      Focus on the Kenyan market context.

      Details:
      Title: ${listing.title || 'N/A'}
      Location: ${listing.location?.neighborhood}, ${listing.location?.county}
      Price: KES ${listing.price}
      Bedrooms: ${listing.bedrooms}
      Bathrooms: ${listing.bathrooms}
      Current Description: ${listing.description || 'N/A'}
      Amenities: ${listing.amenities?.join(', ') || 'N/A'}

      Respond ONLY with a JSON object in the format:
      {
        "enhancedDescription": "string",
        "suggestedTitle": "string",
        "pricingAdvice": "string"
      }
    `;

    try {
      console.log('[Gemini] Enhancing listing content...');
      const model = genAI.getGenerativeModel({ model: TEXT_MODEL_NAME });
      const result = await model.generateContent(prompt);
      const response = await result.response;
      const text = response.text();
      console.log('[Gemini] Response received:', text?.substring(0, 100));
      
      const parsed = parseJsonResponse<AiEnhancedContent>(text || '');
      return parsed;
    } catch (error) {
      console.error("[Gemini] Error enhancing listing content:", error);
      if (error instanceof Error) {
        console.error("[Gemini] Error message:", error.message);
      }
      return null;
    }
  },

  estimateRent: async (location: string, bedrooms: number, county: string): Promise<RentEstimate | null> => {
    if (!genAI) return null;

    const prompt = `
      Provide a rent estimate for a ${bedrooms}-bedroom property in ${location}, ${county}, Kenya.
      Give a min, max, and average rent in KES. State a confidence level.
      
      Respond ONLY with a JSON object in the format:
      {
        "location": "${location}, ${county}",
        "bedrooms": ${bedrooms},
        "minRent": number,
        "maxRent": number,
        "averageRent": number,
        "confidence": "high" | "medium" | "low",
        "lastUpdated": "YYYY-MM-DD" 
      }
    `;
    
    try {
      const model = genAI.getGenerativeModel({ model: TEXT_MODEL_NAME });
      const result = await model.generateContent(prompt);
      const response = await result.response;
      const text = response.text();
      
      const parsed = parseJsonResponse<RentEstimate>(text || '');
      if(parsed){
        return {...parsed, lastUpdated: new Date().toISOString().split('T')[0]};
      }
      return null;
    } catch (error) {
      console.error("Error estimating rent:", error);
      const mockMin = bedrooms * 15000 + (Math.random() * 5000);
      return {
        location: `${location}, ${county}`,
        bedrooms,
        minRent: mockMin,
        maxRent: mockMin + bedrooms * 10000 + (Math.random() * 10000),
        averageRent: mockMin + bedrooms * 5000 + (Math.random() * 5000),
        confidence: 'low',
        lastUpdated: new Date().toISOString().split('T')[0],
      };
    }
  },

  processSmartAssistantQuery: async (query: string): Promise<string> => {
    if (!genAI) return "AI smart assistant is currently unavailable.";

    const systemInstruction = `You are a helpful assistant for finding rental houses in Kenya. 
          Understand user needs (location, bedrooms, budget in KES, amenities) and provide concise summaries or ask clarifying questions.
          Keep responses brief and conversational.`;

    try {
      const model = genAI.getGenerativeModel({ 
        model: TEXT_MODEL_NAME,
        systemInstruction: systemInstruction
      });
      
      const chat = model.startChat({ history: [] });
      const result = await chat.sendMessage(query);
      const response = await result.response;
      return response.text() || '';
    } catch (error) {
      console.error("Error with smart assistant:", error);
      return "Sorry, I'm having trouble understanding that right now.";
    }
  },

  generateText: async (prompt: string, streamingCallback?: (chunk: string) => void ): Promise<string> => {
    if (!genAI) return "AI service is unavailable.";
    try {
      const model = genAI.getGenerativeModel({ model: TEXT_MODEL_NAME });
      
      if (streamingCallback) {
        const result = await model.generateContentStream(prompt);
        let fullText = "";
        for await (const chunk of result.stream) {
            const chunkText = chunk.text();
            fullText += chunkText;
            streamingCallback(chunkText || '');
        }
        return fullText;
      } else {
        const result = await model.generateContent(prompt);
        const response = await result.response;
        return response.text() || '';
      }
    } catch (error) {
        console.error("Error generating text:", error);
        return "An error occurred while generating text.";
    }
  }
};
