"use client";

import { useState, useRef, useEffect } from "react";
import { motion } from "framer-motion";
import Link from "next/link";

interface Message {
  id: string;
  role: "user" | "assistant";
  content: string;
  timestamp: Date;
}

export default function ChatPage() {
  const [messages, setMessages] = useState<Message[]>([
    {
      id: "1",
      role: "assistant",
      content:
        "Xin chào! Tôi là CivicAI, trợ lý điều phối của CityResQ360. Tôi có thể giúp bạn chuẩn hóa biên bản phản ánh, phân loại mức khẩn cấp, đề xuất quy trình xử lý và kích hoạt đội phản ứng phù hợp.",
      timestamp: new Date(),
    },
  ]);
  const [inputValue, setInputValue] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const handleSend = async () => {
    if (!inputValue.trim() || isLoading) return;

    const userMessage: Message = {
      id: Date.now().toString(),
      role: "user",
      content: inputValue,
      timestamp: new Date(),
    };

    setMessages(prev => [...prev, userMessage]);
    setInputValue("");
    setIsLoading(true);

    // Simulate AI response
    setTimeout(() => {
      const aiResponse: Message = {
        id: (Date.now() + 1).toString(),
        role: "assistant",
        content: `CivicAI đang mô phỏng phản hồi cho: "${inputValue}". Tôi sẽ gợi ý phân loại sự cố, mức SLA, đơn vị phụ trách và thông báo mà bạn có thể gửi tới cư dân.`,
        timestamp: new Date(),
      };
      setMessages(prev => [...prev, aiResponse]);
      setIsLoading(false);
    }, 1000);
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  return (
    <div className="flex flex-col h-screen bg-gray-50 dark:bg-gray-900">
      {/* Header */}
      <header className="bg-white dark:bg-gray-950 border-b border-gray-200 dark:border-gray-800">
        <div className="px-4 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Link href="/" className="flex items-center gap-2">
                <img src="/images/logo/logo.svg" alt="CityResQ360" className="h-8 w-auto" />
              <span className="text-lg font-bold bg-gradient-to-r from-brand-600 to-brand-500 bg-clip-text text-transparent">
                  CityResQ360
                </span>
              </Link>
              <span className="text-gray-400">|</span>
              <h1 className="text-xl font-semibold text-gray-900 dark:text-white">
              🤖 CivicAI Copilot
              </h1>
            </div>
            <nav className="hidden md:flex items-center gap-6">
              <Link href="/map" className="text-sm text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">
                Bản đồ
              </Link>
              <Link href="/actions" className="text-sm text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">
                Chiến dịch
              </Link>
              <Link href="/recommendations" className="text-sm text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">
                Kịch bản AI
              </Link>
            </nav>
          </div>
        </div>
      </header>

      {/* Chat Container */}
      <div className="flex-1 overflow-hidden flex flex-col">
        {/* Messages */}
        <div className="flex-1 overflow-y-auto px-4 py-6 mx-auto max-w-4xl w-full">
          <div className="space-y-4">
            {messages.map((message) => (
              <motion.div
                key={message.id}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                className={`flex ${message.role === "user" ? "justify-end" : "justify-start"}`}
              >
                <div
                  className={`max-w-[80%] rounded-xl p-4 ${
                    message.role === "user"
                      ? "bg-gradient-to-r from-brand-500 to-brand-600 text-white"
                      : "bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700"
                  }`}
                >
                  <p className="whitespace-pre-wrap break-words">{message.content}</p>
                  <p className={`mt-2 text-xs ${
                    message.role === "user" ? "text-white/70" : "text-gray-500 dark:text-gray-400"
                  }`}>
                    {message.timestamp.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" })}
                  </p>
                </div>
              </motion.div>
            ))}
            {isLoading && (
              <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                className="flex justify-start"
              >
                <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                  <div className="flex gap-2">
                    <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: "0ms" }} />
                    <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: "150ms" }} />
                    <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: "300ms" }} />
                  </div>
                </div>
              </motion.div>
            )}
            <div ref={messagesEndRef} />
          </div>
        </div>

        {/* Input */}
        <div className="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
          <div className="px-4 py-4 mx-auto max-w-4xl">
            <div className="flex gap-3">
              <input
                type="text"
                value={inputValue}
                onChange={(e) => setInputValue(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder="Nhập câu hỏi về phản ánh, cảnh báo hoặc quy trình xử lý sự cố..."
                className="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                disabled={isLoading}
              />
              <motion.button
                onClick={handleSend}
                disabled={!inputValue.trim() || isLoading}
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                className="px-6 py-3 font-semibold text-white transition-all rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 hover:shadow-lg hover:shadow-brand-500/50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Gửi
              </motion.button>
            </div>
            <p className="mt-2 text-xs text-gray-500 dark:text-gray-400 text-center">
              Nhấn Enter để gửi, Shift+Enter để xuống dòng
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

